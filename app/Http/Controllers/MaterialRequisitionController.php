<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\Project;
use App\Services\ProcurementService;
use App\Services\ProjectHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaterialRequisitionController extends Controller
{
    protected $procurementService;
    protected $historyService;

    public function __construct(ProcurementService $procurementService, ProjectHistoryService $historyService)
    {
        $this->procurementService = $procurementService;
        $this->historyService = $historyService;
    }

    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['project', 'requestedBy', 'approvedBy']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id') && $request->project_id != '') {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                  ->orWhereHas('project', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('requestedBy', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $purchaseRequests = $query->latest()->paginate(15)->withQueryString();

        return view('purchase_requests.index', compact('purchaseRequests'));
    }

    public function create(Request $request)
    {
        $project = null;
        if ($request->has('project_id')) {
            $project = Project::findOrFail($request->project_id);
        }
        // Exclude completed projects - you shouldn't create purchase requests for completed projects
        $projectsQuery = Project::where('status', '!=', 'completed');
        
        // Filter projects for project managers - show only their assigned projects
        if (auth()->user()->hasRole('project_manager')) {
            $projectsQuery->where('project_manager_id', auth()->id());
        }
        
        $projects = $projectsQuery->orderBy('name')->get();
        return view('purchase_requests.create', compact('project', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'purpose' => 'required|string|min:10',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ], [
            'project_id.required' => 'Please select a project for this purchase request.',
            'purpose.required' => 'Please provide a purpose for this purchase request.',
            'purpose.min' => 'Purpose must be at least 10 characters.',
        ]);

        // Check for duplicate items
        $itemIds = array_column($validated['items'], 'inventory_item_id');
        $duplicates = array_diff_assoc($itemIds, array_unique($itemIds));
        
        if (!empty($duplicates)) {
            $duplicateIndexes = array_keys($duplicates);
            $firstDuplicateIndex = $duplicateIndexes[0];
            return back()->withErrors([
                "items.{$firstDuplicateIndex}.inventory_item_id" => 'This item has already been selected. Each item can only be added once to a purchase request.'
            ])->withInput();
        }

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

        // Record project history
        $project = Project::findOrFail($validated['project_id']);
        $this->historyService->recordRelatedEvent(
            $project,
            'purchase_request_created',
            'Purchase Request Created',
            "Purchase request {$pr->pr_number} was created for this project",
            PurchaseRequest::class,
            $pr->id
        );

        return redirect()->route('purchase-requests.show', $pr)->with('success', 'Purchase request created successfully.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load([
            'project', 
            'requestedBy', 
            'approvedBy', 
            'items.inventoryItem',
            'quotations.supplier',
            'quotations.createdBy'
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

