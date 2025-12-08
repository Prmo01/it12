<?php

namespace App\Http\Controllers;

use App\Models\MaterialIssuance;
use App\Models\Project;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaterialIssuanceController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
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

        return view('material_issuance.create', compact('project'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'work_order_number' => 'nullable|string|max:255',
            'issuance_type' => 'required|in:project,maintenance,general,repair,other',
            'issuance_date' => 'required|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

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

        return redirect()->route('material-issuance.show', $issuance)->with('success', 'Material issuance created successfully.');
    }

    public function show(MaterialIssuance $materialIssuance)
    {
        $materialIssuance->load(['project', 'items.inventoryItem', 'requestedBy', 'approvedBy', 'issuedBy']);
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
            'issued_by' => auth()->id(),
            'issued_at' => now(),
        ]);

        // Reload with relationships needed for stock processing
        $materialIssuance->load(['items']);

        $this->stockService->processMaterialIssuance($materialIssuance);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Materials issued and stock updated.');
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

