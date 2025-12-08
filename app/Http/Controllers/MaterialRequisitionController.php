<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\Project;
use App\Services\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaterialRequisitionController extends Controller
{
    protected $procurementService;

    public function __construct(ProcurementService $procurementService)
    {
        $this->procurementService = $procurementService;
    }

    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['project', 'requestedBy', 'approvedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $purchaseRequests = $query->latest()->paginate(15);

        return view('purchase_requests.index', compact('purchaseRequests'));
    }

    public function create(Request $request)
    {
        $project = null;
        if ($request->has('project_id')) {
            $project = Project::findOrFail($request->project_id);
        }
        $projects = Project::orderBy('name')->get();
        return view('purchase_requests.create', compact('project', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ]);

        $validated['pr_number'] = 'PR-' . strtoupper(Str::random(8));
        $validated['status'] = 'draft';
        $validated['requested_by'] = auth()->id();

        // Ensure unit_cost defaults to 0 if null or empty
        if (isset($validated['items'])) {
            foreach ($validated['items'] as &$item) {
                if (!isset($item['unit_cost']) || $item['unit_cost'] === null || $item['unit_cost'] === '') {
                    $item['unit_cost'] = 0;
                }
            }
        }

        $pr = $this->procurementService->createPurchaseRequest($validated);

        return redirect()->route('purchase-requests.show', $pr)->with('success', 'Purchase request created successfully.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load([
            'project', 
            'requestedBy', 
            'approvedBy', 
            'items.inventoryItem',
            'quotations.supplier'
        ]);
        return view('purchase_requests.show', compact('purchaseRequest'));
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->procurementService->approvePurchaseRequest($purchaseRequest, auth()->id());
        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', 'Purchase request approved.');
    }

    public function submit(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->update(['status' => 'submitted']);
        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', 'Purchase request submitted.');
    }

    public function cancel(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
        ]);

        // Check if PR has quotations
        if ($purchaseRequest->quotations()->exists()) {
            return redirect()->back()->with('error', 'Cannot cancel purchase request that has associated quotations. Please cancel the quotations first.');
        }

        // Update status to cancelled instead of deleting
        $purchaseRequest->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', 'Purchase request cancelled successfully.');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        // Check if PR has quotations
        if ($purchaseRequest->quotations()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete purchase request that has associated quotations.');
        }

        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase request deleted successfully.');
    }
}

