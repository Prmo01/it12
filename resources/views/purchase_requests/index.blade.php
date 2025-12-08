@extends('layouts.app')

@section('title', 'Purchase Requests')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-file-earmark-text"></i> Purchase Requests</h1>
        <p class="text-muted mb-0">Manage and track all purchase requests</p>
    </div>
    <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New PR</a>
</div>

<div class="card pr-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>PR Number</th>
                        <th>Project</th>
                        <th>Requested By</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $pr)
                        <tr>
                            <td><span class="text-muted font-monospace">{{ $pr->pr_number }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $pr->project->name ?? 'N/A' }}</div>
                            </td>
                            <td>{{ $pr->requestedBy->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $pr->status === 'approved' ? 'success' : ($pr->status === 'submitted' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($pr->status) }}
                                </span>
                            </td>
                            <td><span class="text-muted">{{ $pr->created_at->format('M d, Y') }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('purchase-requests.show', $pr) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($pr->status === 'submitted')
                                        <form method="POST" action="{{ route('purchase-requests.approve', $pr) }}" class="d-inline" onsubmit="return confirm('Approve this purchase request?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-action" style="background: #10b981; color: #ffffff;" title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($pr->status === 'draft')
                                        <form method="POST" action="{{ route('purchase-requests.submit', $pr) }}" class="d-inline" onsubmit="return confirm('Submit this purchase request for approval?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-action" style="background: #2563eb; color: #ffffff;" title="Submit">
                                                <i class="bi bi-send"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('purchase-requests.approve', $pr) }}" class="d-inline" onsubmit="return confirm('Approve this purchase request directly?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-action" style="background: #10b981; color: #ffffff;" title="Approve Directly">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($pr->status !== 'cancelled' && !$pr->quotations()->exists())
                                    <form action="{{ route('purchase-requests.cancel', $pr) }}" method="POST" class="d-inline cancel-form" data-id="{{ $pr->id }}">
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
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-x"></i>
                                    <p class="mt-3 mb-0">No purchase requests found</p>
                                    <small class="text-muted">Create your first purchase request to get started</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $purchaseRequests->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .pr-card {
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
                if (confirm('Are you sure you want to cancel this Purchase Request?')) {
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
