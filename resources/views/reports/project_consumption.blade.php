@extends('layouts.app')

@section('title', 'Project Consumption Report')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-graph-down"></i> Project Consumption Report</h1>
        <p class="text-muted mb-0">Track material consumption by project</p>
    </div>
    <div class="d-flex gap-2">
        @if($data->isNotEmpty() && isset($filters['project_id']))
        <a href="{{ route('reports.project-consumption', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.project-consumption', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card report-card">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.project-consumption') }}" class="mb-4 filter-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label-custom-small">
                        <i class="bi bi-folder"></i> Project <span class="text-danger">*</span>
                    </label>
                    <select name="project_id" class="form-control-custom" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }} ({{ $project->project_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-calendar-event"></i> Date From
                    </label>
                    <input type="date" name="date_from" class="form-control-custom" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-calendar-check"></i> Date To
                    </label>
                    <input type="date" name="date_to" class="form-control-custom" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['project_id', 'date_from', 'date_to']))
            <div class="mt-2">
                <a href="{{ route('reports.project-consumption') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
            @endif
        </form>
        
        @if(isset($filters['project_id']) && $filters['project_id'])
            @php
                $selectedProject = $projects->firstWhere('id', $filters['project_id']);
            @endphp
            @if($selectedProject)
            <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Project:</strong> {{ $selectedProject->name }} ({{ $selectedProject->project_code }})
                    </div>
                </div>
            </div>
            @endif
        @endif
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Issuance Date</th>
                        <th>MI Number</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $issuance)
                        @foreach($issuance->items as $item)
                            <tr>
                                <td>
                                    <span class="text-muted">{{ $issuance->issuance_date ? $issuance->issuance_date->format('M d, Y') : 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace">{{ $issuance->issuance_number ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace">{{ $item->inventoryItem->item_code ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->inventoryItem->name }}</div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($item->quantity, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $item->inventoryItem->unit_of_measure ?? '' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-graph-down"></i>
                                    <p class="mt-3 mb-0">No consumption data found</p>
                                    <small class="text-muted">Select a project and apply filters to view consumption data</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($data->isNotEmpty())
        <div class="mt-3 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="fw-semibold text-muted">Total Issuances</div>
                    <div class="h4 mb-0">{{ $data->count() }}</div>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-muted">Total Items Issued</div>
                    <div class="h4 mb-0">{{ $data->sum(function($issuance) { return $issuance->items->count(); }) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="fw-semibold text-muted">Total Quantity</div>
                    <div class="h4 mb-0">{{ number_format($data->sum(function($issuance) { return $issuance->items->sum('quantity'); }), 2) }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .report-card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
    }
    
    .filter-form {
        padding: 1.5rem;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    
    .form-label-custom-small {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label-custom-small i {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        color: #111827;
        background: #ffffff;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        background: #fafbff;
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
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
    }
    
    .alert-info {
        background: #dbeafe;
        border: 1px solid #2563eb;
        border-radius: 12px;
        padding: 1rem;
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
</style>
@endpush
@endsection

