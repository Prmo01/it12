<?php

namespace App\Http\Controllers;

use App\Models\ChangeOrder;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChangeOrderController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $query = ChangeOrder::with(['project', 'requestedBy', 'approvedBy']);

        // Filter for project managers - show only their projects
        if (auth()->user()->hasRole('project_manager')) {
            $query->whereHas('project', function($q) {
                $q->where('project_manager_id', auth()->id());
            });
        }

        if ($request->has('project_id') && $request->project_id != '') {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('change_order_number', 'like', "%{$search}%")
                  ->orWhereHas('project', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%");
                  });
            });
        }

        $changeOrders = $query->latest()->paginate(15)->withQueryString();

        return view('change_orders.index', compact('changeOrders'));
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
        
        return view('change_orders.create', compact('project', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string',
            'reason' => 'required|string',
            'additional_days' => 'required|integer|min:0',
            'additional_cost' => 'required|numeric|min:0',
            'approval_notes' => 'nullable|string',
        ]);

        $validated['change_order_number'] = 'CO-' . strtoupper(Str::random(8));
        $validated['status'] = 'pending';
        $validated['requested_by'] = auth()->id();

        $changeOrder = $this->projectService->createChangeOrder($validated);

        return redirect()->route('change-orders.show', $changeOrder)->with('success', 'Change order created successfully.');
    }

    public function show(ChangeOrder $changeOrder)
    {
        $changeOrder->load(['project', 'requestedBy', 'approvedBy']);
        return view('change_orders.show', compact('changeOrder'));
    }

    public function approve(Request $request, ChangeOrder $changeOrder)
    {
        $this->projectService->approveChangeOrder($changeOrder, auth()->id());
        return redirect()->route('change-orders.show', $changeOrder)->with('success', 'Change order approved.');
    }

    public function reject(Request $request, ChangeOrder $changeOrder)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        
        $changeOrder->update([
            'status' => 'rejected',
            'approval_notes' => $request->rejection_reason,
        ]);

        return redirect()->route('change-orders.show', $changeOrder)->with('success', 'Change order rejected.');
    }

    public function cancel(Request $request, ChangeOrder $changeOrder)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
        ]);

        // Check if change order is approved
        if ($changeOrder->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot cancel approved change order.');
        }

        // Update status to cancelled instead of deleting
        $changeOrder->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('change-orders.index')->with('success', 'Change order cancelled successfully.');
    }

    public function destroy(ChangeOrder $changeOrder)
    {
        // Check if change order is approved
        if ($changeOrder->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot delete approved change order.');
        }

        $changeOrder->delete();

        return redirect()->route('change-orders.index')->with('success', 'Change order deleted successfully.');
    }
}

