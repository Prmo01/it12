@extends('layouts.app')

@section('title', 'Completed Projects')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-check-circle"></i> Completed Projects</h1>
        <p class="text-muted mb-0">View all completed construction projects</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Projects</a>
    </div>
</div>

<div class="card project-card">
    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-9">
                    <div class="input-group-custom">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search" class="form-control-custom" placeholder="Search completed projects..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Search</button>
                </div>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Manager</th>
                        <th>Start Date</th>
                        <th>Completed Date</th>
                        <th>Duration</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td><span class="text-muted font-monospace">{{ $project->project_code }}</span></td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 300px;" title="{{ $project->name }}">{{ $project->name }}</div>
                                @if($project->description)
                                    <small class="text-muted d-block text-truncate" style="max-width: 300px;" title="{{ $project->description }}">{{ \Illuminate\Support\Str::limit($project->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($project->projectManager)
                                    <div class="fw-semibold">{{ $project->projectManager->name }}</div>
                                    @if($project->projectManager->role)
                                        <small class="text-muted">{{ $project->projectManager->role->name }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td><span class="text-muted">{{ $project->start_date->format('M d, Y') }}</span></td>
                            <td>
                                <span class="text-success fw-semibold">
                                    {{ $project->actual_end_date ? $project->actual_end_date->format('M d, Y') : $project->end_date->format('M d, Y') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $endDate = $project->actual_end_date ?? $project->end_date;
                                    $duration = $project->start_date->diffInDays($endDate);
                                @endphp
                                <span class="text-muted">{{ $duration }} days</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="text-success fw-semibold">100%</span>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-check-circle"></i>
                                    <p class="mt-3 mb-0">No completed projects found</p>
                                    <small class="text-muted">Completed projects will appear here</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $projects->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .project-card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
    }
    
    .input-group-custom {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        z-index: 1;
    }
    
    .form-control-custom {
        padding-left: 2.75rem;
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    
    .form-control-custom:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .table-modern {
        margin-bottom: 0;
    }
    
    .table-modern thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem;
    }
    
    .table-modern tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
        transform: scale(1.001);
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-action {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-view {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .btn-view:hover {
        background: #2563eb;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    }
    
    .empty-state {
        padding: 2rem;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #9ca3af;
    }
    
    .empty-state p {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
    }
    
    .progress {
        border-radius: 4px;
        overflow: hidden;
    }
</style>
@endpush
@endsection

