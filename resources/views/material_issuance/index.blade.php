@extends('layouts.app')

@section('title', 'Goods Issue')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-right"></i> Goods Issue</h1>
        <p class="text-muted mb-0">Track material issuances for projects and work orders</p>
    </div>
    <a href="{{ route('material-issuance.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Goods Issue</a>
</div>

<div class="card gi-card">
    <div class="card-body">
        <form method="GET" class="mb-4 filter-form">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-funnel"></i> Filter by Status
                    </label>
                    <select name="status" class="form-control-custom">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-tag"></i> Issuance Type
                    </label>
                    <select name="issuance_type" class="form-control-custom">
                        <option value="">All Types</option>
                        <option value="project" {{ request('issuance_type') == 'project' ? 'selected' : '' }}>Project</option>
                        <option value="maintenance" {{ request('issuance_type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="general" {{ request('issuance_type') == 'general' ? 'selected' : '' }}>General</option>
                        <option value="repair" {{ request('issuance_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="other" {{ request('issuance_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-search"></i> Work Order Number
                    </label>
                    <input type="text" name="work_order_number" class="form-control-custom" placeholder="Search work order..." value="{{ request('work_order_number') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    @if(request('status') || request('issuance_type') || request('work_order_number'))
                    <a href="{{ route('material-issuance.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                    @endif
                </div>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Issuance Number</th>
                        <th>Project</th>
                        <th>Type</th>
                        <th>Work Order</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issuances as $issuance)
                        <tr>
                            <td><span class="text-muted font-monospace">{{ $issuance->issuance_number }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $issuance->project->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($issuance->issuance_type ?? 'project') }}</span>
                            </td>
                            <td>
                                <span class="font-monospace">{{ $issuance->work_order_number ?? 'N/A' }}</span>
                            </td>
                            <td><span class="text-muted">{{ $issuance->issuance_date->format('M d, Y') }}</span></td>
                            <td>
                                <span class="badge badge-{{ $issuance->status === 'issued' ? 'success' : ($issuance->status === 'approved' ? 'primary' : ($issuance->status === 'completed' ? 'info' : 'warning')) }}">
                                    {{ ucfirst($issuance->status) }}
                                </span>
                            </td>
                            <td>{{ $issuance->requestedBy->name ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('material-issuance.show', $issuance) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($issuance->status !== 'cancelled' && $issuance->status !== 'issued')
                                    <form action="{{ route('material-issuance.cancel', $issuance) }}" method="POST" class="d-inline cancel-form" data-id="{{ $issuance->id }}">
                                        @csrf
                                        <input type="hidden" name="cancellation_reason" class="cancel-reason-input">
                                        <button type="button" class="btn btn-sm btn-action btn-warning cancel-btn" title="Cancel">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <p class="mt-3 mb-0">No goods issues found</p>
                                    <small class="text-muted">Create your first goods issue to get started</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $issuances->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .gi-card {
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
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
        transform: scale(1.001);
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
    
    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .btn-danger:hover {
        background: #dc2626;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
    }
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-primary {
        background: #2563eb;
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
    
    .btn-warning {
        background: #fef3c7;
        color: #d97706;
    }
    
    .btn-warning:hover {
        background: #d97706;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(217, 119, 6, 0.3);
    }
    
    /* Improved Modal Styling - Fixed Glitches */
    .modal {
        z-index: 1055 !important;
    }
    
    .modal-backdrop {
        z-index: 1050 !important;
        background-color: rgba(0, 0, 0, 0.6) !important;
    }
    
    .modal-dialog {
        z-index: 1056 !important;
        margin: 1.75rem auto;
    }
    
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('.cancel-form');
                if (confirm('Are you sure you want to cancel this Material Issuance?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        form.querySelector('.cancel-reason-input').value = reason.trim();
                        form.submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            });
        });
    });
</script>
@endpush

@endsection
