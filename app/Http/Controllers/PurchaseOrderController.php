<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\ProcurementService;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    use LogsAudit;

    protected $procurementService;

    public function __construct(ProcurementService $procurementService)
    {
        $this->procurementService = $procurementService;
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['purchaseRequest', 'items.supplier'])
            ->whereDoesntHave('goodsReceipts', function ($q) {
                $q->where('status', 'approved'); // Exclude POs that have approved goods receipts
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->latest()->paginate(15);

        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function pending(Request $request)
    {
        $query = PurchaseOrder::with(['purchaseRequest', 'items.supplier'])
            ->where('status', 'pending')
            ->whereDoesntHave('goodsReceipts', function ($q) {
                $q->where('status', 'approved');
            });

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->latest()->paginate(15);

        return view('purchase_orders.pending', compact('purchaseOrders'));
    }

    public function create(Request $request)
    {
        $quotation = null;
        if ($request->has('quotation_id')) {
            $quotation = Quotation::with(['items.inventoryItem', 'items.supplier', 'purchaseRequest'])->findOrFail($request->quotation_id);
        }
        return view('purchase_orders.create', compact('quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'expected_delivery_date' => 'nullable|date',
            'terms_conditions' => 'nullable|string',
            'delivery_address' => 'nullable|string',
        ]);

        $quotation = Quotation::findOrFail($validated['quotation_id']);
        $po = $this->procurementService->createPurchaseOrderFromQuotation($quotation, $validated);

        return redirect()->route('purchase-orders.show', $po)->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['purchaseRequest', 'quotation', 'items.inventoryItem', 'items.supplier', 'createdBy', 'approvedBy']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->procurementService->approvePurchaseOrder($purchaseOrder, auth()->id());
        
        // Refresh to get updated status
        $purchaseOrder->refresh();
        
        // Log approval
        $this->logApproved($purchaseOrder, "Purchase Order {$purchaseOrder->po_number} approved");
        
        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order approved.');
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items.inventoryItem', 'items.supplier', 'createdBy', 'approvedBy', 'supplier', 'purchaseRequest.project']);
        $printedBy = auth()->user();
        $pdf = Pdf::loadView('purchase_orders.print', compact('purchaseOrder', 'printedBy'));
        return $pdf->download("PO-{$purchaseOrder->po_number}.pdf");
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
        ]);

        // Check if PO has approved goods receipts
        if ($purchaseOrder->goodsReceipts()->where('status', 'approved')->exists()) {
            return redirect()->back()->with('error', 'Cannot cancel purchase order that has approved goods receipts.');
        }

        // Cancel the PO with reason
        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        // Log cancellation
        $this->logCancelled($purchaseOrder, "Purchase Order {$purchaseOrder->po_number} cancelled: {$validated['cancellation_reason']}");

        // Revert quotation status back to pending/accepted if it exists
        if ($purchaseOrder->quotation) {
            $quotation = $purchaseOrder->quotation;
            // If quotation was accepted, revert it back to accepted
            // If it was pending, keep it pending
            // We'll set it to pending so it can be used again
            if ($quotation->status === 'accepted') {
                $quotation->update(['status' => 'pending']);
            }
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order cancelled successfully. The quotation has been reverted to pending status.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Check if PO has approved goods receipts
        if ($purchaseOrder->goodsReceipts()->where('status', 'approved')->exists()) {
            return redirect()->back()->with('error', 'Cannot delete purchase order that has approved goods receipts.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted successfully.');
    }
}

