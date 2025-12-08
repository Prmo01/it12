@extends('layouts.app')

@section('title', 'Add Additional Project')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-plus-circle"></i> Add Additional Project</h1>
        <p class="text-muted mb-0">Manage additional project changes and modifications</p>
    </div>
    <a href="{{ route('change-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add</a>
</div>

<div class="card change-orders-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Change Order Number</th>
                        <th>Project</th>
                        <th>Description</th>
                        <th>Additional Days</th>
                        <th>Additional Cost</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($changeOrders as $co)
                        <tr>
                            <td><span class="text-muted font-monospace">{{ $co->change_order_number }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $co->project->name }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($co->description, 50) }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $co->additional_days }} days</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-success">₱{{ number_format($co->additional_cost, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $co->status === 'approved' ? 'success' : ($co->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($co->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('change-orders.show', $co) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($co->status !== 'cancelled' && $co->status !== 'approved')
                                    <form action="{{ route('change-orders.cancel', $co) }}" method="POST" class="d-inline cancel-form" data-id="{{ $co->id }}">
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
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-plus"></i>
                                    <p class="mt-3 mb-0">No additional projects found</p>
                                    <small class="text-muted">Add an additional project to track project modifications</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $changeOrders->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .change-orders-card {
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
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
        transform: scale(1.001);
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('.cancel-form');
                if (confirm('Are you sure you want to cancel this Change Order?')) {
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
