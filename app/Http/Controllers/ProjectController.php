<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $query = Project::with(['projectManager.role'])
            ->where('status', '!=', 'completed'); // Exclude completed projects

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(15);

        return view('projects.index', compact('projects'));
    }

    public function completed(Request $request)
    {
        $query = Project::with(['projectManager.role'])
            ->where('status', 'completed'); // Only completed projects

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest('actual_end_date')->paginate(15);

        return view('projects.completed', compact('projects'));
    }

    public function create()
    {
        $users = User::with('role')->orderBy('name')->get();
        return view('projects.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_manager_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        $validated['project_code'] = 'PRJ-' . strtoupper(Str::random(8));
        $validated['status'] = 'planning';

        $project = $this->projectService->createProject($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load([
            'projectManager.role', 
            'changeOrders', 
            'purchaseRequests.requestedBy',
            'purchaseRequests.quotations.supplier',
            'purchaseRequests.purchaseOrders.supplier',
            'materialIssuances'
        ]);
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $users = User::with('role')->orderBy('name')->get();
        return view('projects.edit', compact('project', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_manager_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'actual_cost' => 'nullable|numeric|min:0',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $this->projectService->updateProject($project, $validated);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function markAsDone(Project $project)
    {
        // Check if project is already completed
        if ($project->status === 'completed') {
            return redirect()->route('projects.completed')->with('info', 'This project is already marked as done.');
        }

        // Mark project as completed
        $project->update([
            'status' => 'completed',
            'actual_end_date' => now(),
            'progress_percentage' => 100,
        ]);

        return redirect()->route('projects.completed')->with('success', 'Project marked as done and moved to completed projects!');
    }

    public function cancel(Request $request, Project $project)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
        ]);

        // Check if project is already completed
        if ($project->status === 'completed') {
            return redirect()->back()->with('error', 'Cannot cancel completed project.');
        }

        // Update status to cancelled instead of deleting
        $project->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return redirect()->route('projects.index')->with('success', 'Project cancelled successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}

