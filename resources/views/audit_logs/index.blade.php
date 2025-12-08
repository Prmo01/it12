@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-journal-text"></i> Audit Logs</h1>
        <p class="text-muted mb-0">Track all system activities and changes</p>
    </div>
</div>

<div class="card audit-card">
    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="input-group-custom">
                        <i class="bi bi-search input-icon"></i>
                        <input type="text" name="search" class="form-control-custom" placeholder="Search logs..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select-custom">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="model_type" class="form-select-custom">
                        <option value="">All Models</option>
                        @foreach($modelTypes as $modelType)
                            @php
                                $modelName = class_basename($modelType);
                            @endphp
                            <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                                {{ $modelName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control-custom" value="{{ request('date_from') }}" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control-custom" value="{{ request('date_to') }}" placeholder="To Date">
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Clear</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <span class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="fw-semibold">{{ $log->user->name }}</div>
                                    <small class="text-muted">{{ $log->user->role->name ?? 'N/A' }}</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->getActionColor() }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>
                                <span class="font-monospace">{{ class_basename($log->model_type) }}</span>
                                <small class="text-muted d-block">ID: {{ $log->model_id }}</small>
                            </td>
                            <td>
                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($log->description ?? 'N/A', 50) }}</span>
                            </td>
                            <td>
                                <span class="text-muted font-monospace">{{ $log->ip_address ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-action btn-view" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-journal-x"></i>
                                    <p class="mt-3 mb-0">No audit logs found</p>
                                    <small class="text-muted">No activities match your search criteria</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $logs->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .audit-card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
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
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-danger {
        background: #ef4444;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-warning {
        background: #f59e0b;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-info {
        background: #3b82f6;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-secondary {
        background: #6b7280;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .empty-state {
        padding: 2rem;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #9ca3af;
    }
    
    .empty-state p {
        font-size: 1rem;
        color: #374151;
        margin: 0;
    }
    
    .input-group-custom {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        z-index: 1;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9375rem;
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .form-select-custom {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9375rem;
    }
    
    .form-select-custom:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
    }
</style>
@endpush
@endsection

