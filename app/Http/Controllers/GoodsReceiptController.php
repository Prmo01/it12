<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoodsReceiptController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder.items.supplier', 'purchaseOrder.purchaseRequest.project', 'receivedBy', 'approvedBy', 'warehouseApprovedBy', 'inventoryApprovedBy']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('purchase_order_id') && $request->purchase_order_id != '') {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        if ($request->has('supplier_id') && $request->supplier_id != '') {
            $query->whereHas('purchaseOrder.items', function($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('gr_number', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhere('delivery_note_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function($q) use ($search) {
                      $q->where('po_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('purchaseOrder.items.supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $goodsReceipts = $query->latest()->paginate(15)->withQueryString();

        return view('goods_receipts.index', compact('goodsReceipts'));
    }

    public function create(Request $request)
    {
        $purchaseOrder = null;
        $purchaseOrders = null;
        
        if ($request->has('purchase_order_id')) {
            $purchaseOrder = PurchaseOrder::with(['items.inventoryItem', 'supplier'])->findOrFail($request->purchase_order_id);
            
            // Check if PO already has an approved goods receipt
            if ($purchaseOrder->goodsReceipts()->where('status', 'approved')->exists()) {
                return redirect()->route('goods-receipts.index')
                    ->with('error', 'This purchase order already has an approved goods receipt.');
            }
        } else {
            // Get approved purchase orders that don't have approved goods receipts
            $purchaseOrders = PurchaseOrder::with(['supplier', 'items.inventoryItem'])
                ->where('status', 'approved')
                ->whereDoesntHave('goodsReceipts', function ($q) {
                    $q->where('status', 'approved');
                })
                ->orderBy('po_date', 'desc')
                ->get();
        }
        
        return view('goods_receipts.create', compact('purchaseOrder', 'purchaseOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'gr_date' => 'required|date',
            'delivery_note_number' => 'required|string|min:3',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.quantity_accepted' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string',
        ]);
        
        // Normalize items data - set default values for missing fields
        foreach ($validated['items'] as $key => $item) {
            $validated['items'][$key]['quantity_rejected'] = $item['quantity_rejected'] ?? 0;
            $validated['items'][$key]['rejection_reason'] = $item['rejection_reason'] ?? null;
        }

        $validated['gr_number'] = 'GR-' . strtoupper(Str::random(8));
        $validated['status'] = 'pending';
        $validated['received_by'] = auth()->id();

        // Get project_code from purchase_order
        $purchaseOrder = PurchaseOrder::with('purchaseRequest.project')->findOrFail($validated['purchase_order_id']);
        
        // Check if PO already has an approved goods receipt
        if ($purchaseOrder->goodsReceipts()->where('status', 'approved')->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This purchase order already has an approved goods receipt.');
        }
        
        if ($purchaseOrder->project_code) {
            $validated['project_code'] = $purchaseOrder->project_code;
        } elseif ($purchaseOrder->purchaseRequest && $purchaseOrder->purchaseRequest->project) {
            $validated['project_code'] = $purchaseOrder->purchaseRequest->project->project_code;
        }

        $gr = GoodsReceipt::create($validated);

        foreach ($validated['items'] as $item) {
            $gr->items()->create($item);
        }

        return redirect()->route('goods-receipts.show', $gr)->with('success', 'Goods receipt submitted for approval. Waiting for inventory manager approval.');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load(['purchaseOrder', 'items.purchaseOrderItem.supplier', 'items.inventoryItem', 'receivedBy', 'approvedBy', 'warehouseApprovedBy', 'inventoryApprovedBy']);
        return view('goods_receipts.show', compact('goodsReceipt'));
    }

    public function approve(Request $request, GoodsReceipt $goodsReceipt)
    {
        $user = auth()->user();
        
        // Only Inventory Manager can approve and update stock
        if (!$user->hasRole('inventory_manager') && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'Only inventory managers can approve goods receipts and update stock.');
        }

        if ($goodsReceipt->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending goods receipts can be approved.');
        }

        $validated = $request->validate([
            'inventory_feedback' => 'nullable|string|max:1000',
        ]);

        $goodsReceipt->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'inventory_approved_by' => $user->id,
            'inventory_approved_at' => now(),
            'inventory_feedback' => $validated['inventory_feedback'] ?? null,
        ]);

        // Reload with relationships needed for stock processing
        $goodsReceipt->load(['items.purchaseOrderItem']);

        // Update stock when inventory manager approves
        $this->stockService->processGoodsReceipt($goodsReceipt);

        return redirect()->route('goods-receipts.show', $goodsReceipt)
            ->with('success', 'Goods receipt approved and stock updated.');
    }


    public function cancel(Request $request, GoodsReceipt $goodsReceipt)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
        ]);

        // Check if goods receipt is approved (stock already updated)
        if ($goodsReceipt->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot cancel approved goods receipt. Stock has already been updated.');
        }

        // Check if goods receipt has returns
        if ($goodsReceipt->goodsReturns()->exists()) {
            return redirect()->back()->with('error', 'Cannot cancel goods receipt that has associated returns.');
        }

        // Update status to cancelled instead of deleting
        $goodsReceipt->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('goods-receipts.index')->with('success', 'Goods receipt cancelled successfully.');
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        // Check if goods receipt is approved (stock already updated)
        if ($goodsReceipt->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot delete approved goods receipt. Stock has already been updated.');
        }

        // Check if goods receipt has returns
        if ($goodsReceipt->goodsReturns()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete goods receipt that has associated returns.');
        }

        $goodsReceipt->delete();

        return redirect()->route('goods-receipts.index')->with('success', 'Goods receipt deleted successfully.');
    }
}

