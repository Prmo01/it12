<?php

namespace App\Http\Controllers;

use App\Models\MaterialIssuance;
use App\Models\Project;
use App\Services\StockService;
use App\Services\ProjectHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaterialIssuanceController extends Controller
{
    protected $stockService;
    protected $historyService;

    public function __construct(StockService $stockService, ProjectHistoryService $historyService)
    {
        $this->stockService = $stockService;
        $this->historyService = $historyService;
    }

    public function index(Request $request)
    {
        $query = MaterialIssuance::with(['project', 'requestedBy', 'approvedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('issuance_type')) {
            $query->where('issuance_type', $request->issuance_type);
        }

        if ($request->has('work_order_number')) {
            $query->where('work_order_number', 'like', '%' . $request->work_order_number . '%');
        }

        $issuances = $query->latest()->paginate(15);

        return view('material_issuance.index', compact('issuances'));
    }

    public function create(Request $request)
    {
        $project = null;
        
        if ($request->has('project_id')) {
            $project = Project::findOrFail($request->project_id);
        }

        // Get projects for dropdown - filter for project managers
        $projectsQuery = Project::query();
        if (auth()->user()->hasRole('project_manager')) {
            $projectsQuery->where('project_manager_id', auth()->id());
        }
        $projects = $projectsQuery->orderBy('name')->get();

        return view('material_issuance.create', compact('project', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required_if:issuance_type,project|nullable|exists:projects,id',
            'work_order_number' => 'nullable|string|max:255',
            'issuance_type' => 'required|in:project,maintenance,general,repair,other',
            'issuance_date' => 'required|date',
            'purpose' => 'required|string|min:10',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ], [
            'project_id.required_if' => 'Project is required when issuance type is "Project".',
            'purpose.required' => 'Please provide a purpose for this material issuance.',
            'purpose.min' => 'Purpose must be at least 10 characters.',
        ]);
        
        // Check for duplicate inventory_item_id values
        $inventoryItemIds = array_column($validated['items'], 'inventory_item_id');
        if (count($inventoryItemIds) !== count(array_unique($inventoryItemIds))) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['items' => 'Each item can only be selected once. Please remove duplicate items.']);
        }

        $validated['issuance_number'] = 'MI-' . strtoupper(Str::random(8));
        $validated['status'] = 'draft';
        $validated['requested_by'] = auth()->id();

        // Ensure unit_cost defaults to 0 if null or empty for all items
        if (isset($validated['items'])) {
            foreach ($validated['items'] as &$item) {
                if (!isset($item['unit_cost']) || $item['unit_cost'] === null || $item['unit_cost'] === '') {
                    $item['unit_cost'] = 0;
                }
            }
        }

        $issuance = MaterialIssuance::create($validated);

        foreach ($validated['items'] as $item) {
            $issuance->items()->create($item);
        }

        // Record project history if project is associated
        if (isset($validated['project_id']) && $validated['project_id']) {
            $project = Project::find($validated['project_id']);
            if ($project) {
                $this->historyService->recordRelatedEvent(
                    $project,
                    'material_issuance_created',
                    'Material Issuance Created',
                    "Material issuance {$issuance->issuance_number} was created for this project",
                    MaterialIssuance::class,
                    $issuance->id
                );
            }
        }

        return redirect()->route('material-issuance.show', $issuance)->with('success', 'Material issuance created successfully.');
    }

    public function show(MaterialIssuance $materialIssuance)
    {
        $materialIssuance->load([
            'project', 
            'items.inventoryItem', 
            'requestedBy', 
            'approvedBy', 
            'issuedBy',
            'receivedBy'
        ]);
        return view('material_issuance.show', compact('materialIssuance'));
    }

    public function approve(Request $request, MaterialIssuance $materialIssuance)
    {
        $materialIssuance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Material issuance approved.');
    }

    public function issue(Request $request, MaterialIssuance $materialIssuance)
    {
        if ($materialIssuance->status !== 'approved') {
            return redirect()->back()->with('error', 'Material issuance must be approved first.');
        }

        $materialIssuance->update([
            'status' => 'issued',
            'delivery_status' => 'pending', // Set to pending for warehouse validation
            'issued_by' => auth()->id(),
            'issued_at' => now(),
        ]);

        // Reload with relationships needed for stock processing
        $materialIssuance->load(['items']);

        $this->stockService->processMaterialIssuance($materialIssuance);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Materials issued and stock updated. Waiting for warehouse to confirm delivery.');
    }

    public function markDelivered(Request $request, MaterialIssuance $materialIssuance)
    {
        if ($materialIssuance->status !== 'issued') {
            return redirect()->back()->with('error', 'Only issued material issuances can be marked as delivered.');
        }

        if ($materialIssuance->delivery_status === 'received') {
            return redirect()->back()->with('error', 'Material issuance has already been received by warehouse.');
        }

        $materialIssuance->update([
            'delivery_status' => 'delivered',
        ]);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Material issuance marked as delivered. Waiting for warehouse confirmation.');
    }

    public function confirmReceived(Request $request, MaterialIssuance $materialIssuance)
    {
        $validated = $request->validate([
            'received_date' => 'required|date',
        ]);

        if ($materialIssuance->status !== 'issued') {
            return redirect()->back()->with('error', 'Only issued material issuances can be confirmed as received.');
        }

        if ($materialIssuance->delivery_status === 'received') {
            return redirect()->back()->with('error', 'Material issuance has already been confirmed as received.');
        }

        // Only warehouse managers can confirm receipt
        $user = auth()->user();
        if (!$user->hasRole('warehouse_manager') && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'Only warehouse managers can confirm receipt of materials.');
        }

        $materialIssuance->update([
            'delivery_status' => 'received',
            'received_by' => auth()->id(),
            'received_at' => \Carbon\Carbon::parse($validated['received_date']),
        ]);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Material receipt confirmed by warehouse. Items have been validated.');
    }

    public function cancel(Request $request, MaterialIssuance $materialIssuance)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
        ]);

        // Check if material issuance is issued (stock already updated)
        if ($materialIssuance->status === 'issued') {
            return redirect()->back()->with('error', 'Cannot cancel issued material issuance. Stock has already been updated.');
        }

        // Update status to cancelled instead of deleting
        $materialIssuance->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Material issuance cancelled successfully.');
    }

    public function destroy(MaterialIssuance $materialIssuance)
    {
        // Check if material issuance is issued (stock already updated)
        if ($materialIssuance->status === 'issued') {
            return redirect()->back()->with('error', 'Cannot delete issued material issuance. Stock has already been updated.');
        }

        $materialIssuance->delete();

        return redirect()->route('material-issuance.index')->with('success', 'Material issuance deleted successfully.');
    }
}

