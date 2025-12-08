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
        $query = GoodsReceipt::with(['purchaseOrder.items.supplier', 'receivedBy', 'approvedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        $goodsReceipts = $query->latest()->paginate(15);

        return view('goods_receipts.index', compact('goodsReceipts'));
    }

    public function create(Request $request)
    {
        $purchaseOrder = null;
        $purchaseOrders = null;
        
        if ($request->has('purchase_order_id')) {
            $purchaseOrder = PurchaseOrder::with(['items.inventoryItem', 'supplier'])->findOrFail($request->purchase_order_id);
        } else {
            // Get approved purchase orders that haven't been fully received
            $purchaseOrders = PurchaseOrder::with(['supplier', 'items'])
                ->where('status', 'approved')
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
            'delivery_note_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.quantity_accepted' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'required|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string',
        ]);

        $validated['gr_number'] = 'GR-' . strtoupper(Str::random(8));
        $validated['status'] = 'draft';
        $validated['received_by'] = auth()->id();

        // Get project_code from purchase_order
        $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        if ($purchaseOrder->project_code) {
            $validated['project_code'] = $purchaseOrder->project_code;
        } elseif ($purchaseOrder->purchaseRequest && $purchaseOrder->purchaseRequest->project) {
            $validated['project_code'] = $purchaseOrder->purchaseRequest->project->project_code;
        }

        $gr = GoodsReceipt::create($validated);

        foreach ($validated['items'] as $item) {
            $gr->items()->create($item);
        }

        return redirect()->route('goods-receipts.show', $gr)->with('success', 'Goods receipt created successfully.');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load(['purchaseOrder', 'items.purchaseOrderItem.supplier', 'items.inventoryItem', 'receivedBy', 'approvedBy']);
        return view('goods_receipts.show', compact('goodsReceipt'));
    }

    public function approve(Request $request, GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Reload with relationships needed for stock processing
        $goodsReceipt->load(['items.purchaseOrderItem']);

        $this->stockService->processGoodsReceipt($goodsReceipt);

        return redirect()->route('goods-receipts.show', $goodsReceipt)->with('success', 'Goods receipt approved and stock updated.');
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

