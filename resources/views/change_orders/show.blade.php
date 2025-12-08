@extends('layouts.app')

@section('title', 'Add Additional Project Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-plus-circle"></i> Add Additional Project</h1>
        <p class="text-muted mb-0">{{ $changeOrder->change_order_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($changeOrder->status === 'pending')
            <form method="POST" action="{{ route('change-orders.approve', $changeOrder) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
            </form>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle"></i> Reject
            </button>
        @endif
        @if($changeOrder->status !== 'cancelled' && $changeOrder->status !== 'approved')
        <form action="{{ route('change-orders.cancel', $changeOrder) }}" method="POST" class="d-inline" id="cancelCOForm">
            @csrf
            <input type="hidden" name="cancellation_reason" id="cancelCOReason">
            <button type="button" class="btn btn-warning" onclick="cancelCO()">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
        </form>
        <script>
            function cancelCO() {
                if (confirm('Are you sure you want to cancel this Change Order?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        document.getElementById('cancelCOReason').value = reason.trim();
                        document.getElementById('cancelCOForm').submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            }
        </script>
        @endif
        <a href="{{ route('change-orders.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Additional Project Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Change Order Number</span>
                        <span class="info-value font-monospace">{{ $changeOrder->change_order_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $changeOrder->status === 'approved' ? 'success' : ($changeOrder->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($changeOrder->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project</span>
                        <span class="info-value">{{ $changeOrder->project->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Additional Days</span>
                        <span class="info-value">
                            <span class="badge badge-info">{{ $changeOrder->additional_days }} days</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Additional Cost</span>
                        <span class="info-value text-success fw-semibold">₱{{ number_format($changeOrder->additional_cost, 2) }}</span>
                    </div>
                    <div class="info-item full-width">
                        <span class="info-label">Description</span>
                        <span class="info-value">{{ $changeOrder->description }}</span>
                    </div>
                    <div class="info-item full-width">
                        <span class="info-label">Reason</span>
                        <span class="info-value">{{ $changeOrder->reason }}</span>
                    </div>
                    @if($changeOrder->approval_notes)
                    <div class="info-item full-width">
                        <span class="info-label">Approval Notes</span>
                        <span class="info-value">{{ $changeOrder->approval_notes }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="quick-actions-card mb-4">
            <div class="quick-actions-header">
                <h5 class="quick-actions-title"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="quick-actions-body">
                @if($changeOrder->status === 'pending')
                <div class="quick-action-info">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Pending Approval</strong>
                        <p class="mb-0">This additional project is awaiting approval.</p>
                    </div>
                </div>
                @elseif($changeOrder->status === 'approved')
                <div class="quick-action-success">
                    <i class="bi bi-check-circle"></i>
                    <div>
                        <strong>Approved</strong>
                        <p class="mb-0">This additional project has been approved.</p>
                    </div>
                </div>
                @else
                <div class="quick-action-danger">
                    <i class="bi bi-x-circle"></i>
                    <div>
                        <strong>Rejected</strong>
                        <p class="mb-0">This additional project has been rejected.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-briefcase"></i> Related Project</h5>
            </div>
            <div class="info-card-body">
                <div class="related-item">
                    <span class="related-label">Project Name</span>
                    <span class="related-value">{{ $changeOrder->project->name }}</span>
                </div>
                <a href="{{ route('projects.show', $changeOrder->project) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                    <i class="bi bi-eye"></i> View Project
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Additional Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('change-orders.reject', $changeOrder) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-custom">
                            <i class="bi bi-exclamation-triangle"></i> Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" class="form-control-custom" rows="4" placeholder="Enter reason for rejection" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .info-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .info-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-card-body {
        padding: 1.5rem;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .info-item.full-width {
        grid-column: 1 / -1;
    }
    
    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .info-value {
        font-size: 0.9375rem;
        color: #111827;
        font-weight: 500;
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
    
    .quick-actions-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .quick-actions-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    
    .quick-actions-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .quick-actions-body {
        padding: 1rem;
    }
    
    .quick-action-info {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        background: #dbeafe;
        border-radius: 12px;
        border: 1px solid #2563eb;
    }
    
    .quick-action-info i {
        font-size: 1.5rem;
        color: #2563eb;
        flex-shrink: 0;
    }
    
    .quick-action-info strong {
        display: block;
        color: #1e40af;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-info p {
        color: #1e40af;
        margin: 0;
        font-size: 0.875rem;
    }
    
    .quick-action-success {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        background: #d1fae5;
        border-radius: 12px;
        border: 1px solid #10b981;
    }
    
    .quick-action-success i {
        font-size: 1.5rem;
        color: #10b981;
        flex-shrink: 0;
    }
    
    .quick-action-success strong {
        display: block;
        color: #065f46;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-success p {
        color: #065f46;
        margin: 0;
        font-size: 0.875rem;
    }
    
    .quick-action-danger {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        background: #fee2e2;
        border-radius: 12px;
        border: 1px solid #ef4444;
    }
    
    .quick-action-danger i {
        font-size: 1.5rem;
        color: #ef4444;
        flex-shrink: 0;
    }
    
    .quick-action-danger strong {
        display: block;
        color: #991b1b;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-danger p {
        color: #991b1b;
        margin: 0;
        font-size: 0.875rem;
    }
    
    .related-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .related-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .related-value {
        font-size: 0.9375rem;
        color: #111827;
        font-weight: 500;
    }
    
</style>
@endpush

@endsection
