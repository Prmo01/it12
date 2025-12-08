<?php

namespace App\Http\Controllers;

use App\Models\GoodsReturn;
use App\Models\GoodsReceipt;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoodsReturnController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = GoodsReturn::with(['goodsReceipt.purchaseOrder', 'returnedBy', 'approvedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $goodsReturns = $query->latest()->paginate(15);

        return view('goods_returns.index', compact('goodsReturns'));
    }

    public function create(Request $request)
    {
        $goodsReceipt = null;
        $itemReturnData = [];
        
        if ($request->has('goods_receipt_id')) {
            $goodsReceipt = GoodsReceipt::with(['items.inventoryItem', 'purchaseOrder'])
                ->where('status', 'approved')
                ->findOrFail($request->goods_receipt_id);
            
            // Calculate available quantities for each item
            foreach ($goodsReceipt->items as $grItem) {
                $alreadyReturned = \App\Models\GoodsReturnItem::where('goods_receipt_item_id', $grItem->id)
                    ->whereHas('goodsReturn', function($query) {
                        $query->whereIn('status', ['draft', 'pending', 'approved']);
                    })
                    ->sum('quantity');
                
                $itemReturnData[$grItem->id] = [
                    'accepted' => $grItem->quantity_accepted,
                    'already_returned' => $alreadyReturned,
                    'available' => $grItem->quantity_accepted - $alreadyReturned,
                ];
            }
        }
        
        // Get list of approved goods receipts for selection
        $approvedGoodsReceipts = GoodsReceipt::with('purchaseOrder.supplier')
            ->where('status', 'approved')
            ->latest()
            ->get();
        
        return view('goods_returns.create', compact('goodsReceipt', 'approvedGoodsReceipts', 'itemReturnData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id',
            'return_date' => 'required|date',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.goods_receipt_item_id' => 'required|exists:goods_receipt_items,id',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        // Verify goods receipt is approved
        $goodsReceipt = GoodsReceipt::findOrFail($validated['goods_receipt_id']);
        if ($goodsReceipt->status !== 'approved') {
            return back()->withErrors(['goods_receipt_id' => 'Can only create returns from approved goods receipts.'])->withInput();
        }

        // Validate return quantities don't exceed accepted quantities
        foreach ($validated['items'] as $index => $item) {
            $grItem = \App\Models\GoodsReceiptItem::findOrFail($item['goods_receipt_item_id']);
            
            // Check if this item belongs to the selected goods receipt
            if ($grItem->goods_receipt_id != $validated['goods_receipt_id']) {
                return back()->withErrors(['items.' . $index . '.goods_receipt_item_id' => 'Invalid goods receipt item.'])->withInput();
            }
            
            // Calculate already returned quantity
            $alreadyReturned = \App\Models\GoodsReturnItem::where('goods_receipt_item_id', $grItem->id)
                ->whereHas('goodsReturn', function($query) {
                    $query->whereIn('status', ['draft', 'pending', 'approved']);
                })
                ->sum('quantity');
            
            $availableToReturn = $grItem->quantity_accepted - $alreadyReturned;
            
            if ($item['quantity'] > $availableToReturn) {
                return back()->withErrors(['items.' . $index . '.quantity' => "Return quantity cannot exceed available quantity ({$availableToReturn})."])->withInput();
            }
        }

        // Filter out items with zero quantity
        $itemsToReturn = array_filter($validated['items'], function($item) {
            return isset($item['quantity']) && $item['quantity'] > 0;
        });

        if (empty($itemsToReturn)) {
            return back()->withErrors(['items' => 'At least one item with quantity greater than 0 is required.'])->withInput();
        }

        $validated['return_number'] = 'RT-' . strtoupper(Str::random(8));
        $validated['status'] = 'draft';
        $validated['returned_by'] = auth()->id();

        // Get project_code from goods_receipt
        $goodsReceipt = GoodsReceipt::findOrFail($validated['goods_receipt_id']);
        if ($goodsReceipt->project_code) {
            $validated['project_code'] = $goodsReceipt->project_code;
        } elseif ($goodsReceipt->purchaseOrder && $goodsReceipt->purchaseOrder->project_code) {
            $validated['project_code'] = $goodsReceipt->purchaseOrder->project_code;
        } elseif ($goodsReceipt->purchaseOrder && $goodsReceipt->purchaseOrder->purchaseRequest && $goodsReceipt->purchaseOrder->purchaseRequest->project) {
            $validated['project_code'] = $goodsReceipt->purchaseOrder->purchaseRequest->project->project_code;
        }

        $return = GoodsReturn::create($validated);

        foreach ($itemsToReturn as $item) {
            $return->items()->create($item);
        }

        return redirect()->route('goods-returns.show', $return)->with('success', 'Goods return created successfully.');
    }

    public function show(GoodsReturn $goodsReturn)
    {
        $goodsReturn->load(['goodsReceipt.purchaseOrder', 'items.inventoryItem', 'returnedBy', 'approvedBy']);
        return view('goods_returns.show', compact('goodsReturn'));
    }

    public function approve(Request $request, GoodsReturn $goodsReturn)
    {
        $goodsReturn->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        foreach ($goodsReturn->items as $item) {
            $this->stockService->processGoodsReturn($item->inventory_item_id, $item->quantity, $goodsReturn->id);
        }

        return redirect()->route('goods-returns.show', $goodsReturn)->with('success', 'Goods return approved and stock updated.');
    }

    public function cancel(Request $request, GoodsReturn $goodsReturn)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
        ]);

        // Check if goods return is approved (stock already updated)
        if ($goodsReturn->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot cancel approved goods return. Stock has already been updated.');
        }

        // Update status to cancelled instead of deleting
        $goodsReturn->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('goods-returns.index')->with('success', 'Goods return cancelled successfully.');
    }

    public function destroy(GoodsReturn $goodsReturn)
    {
        // Check if goods return is approved (stock already updated)
        if ($goodsReturn->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot delete approved goods return. Stock has already been updated.');
        }

        $goodsReturn->delete();

        return redirect()->route('goods-returns.index')->with('success', 'Goods return deleted successfully.');
    }
}

