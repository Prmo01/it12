@extends('layouts.app')

@section('title', 'Goods Issue Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-right"></i> Goods Issue</h1>
        <p class="text-muted mb-0">{{ $materialIssuance->issuance_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($materialIssuance->status === 'draft')
            <form method="POST" action="{{ route('material-issuance.approve', $materialIssuance) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
            </form>
        @endif
        @if($materialIssuance->status === 'approved')
            <form method="POST" action="{{ route('material-issuance.issue', $materialIssuance) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-box-arrow-up"></i> Issue Goods
                </button>
            </form>
        @endif
        @if($materialIssuance->status !== 'cancelled' && $materialIssuance->status !== 'issued')
        <form action="{{ route('material-issuance.cancel', $materialIssuance) }}" method="POST" class="d-inline" id="cancelMIForm">
            @csrf
            <input type="hidden" name="cancellation_reason" id="cancelMIReason">
            <button type="button" class="btn btn-warning" onclick="cancelMI()">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
        </form>
        <script>
            function cancelMI() {
                if (confirm('Are you sure you want to cancel this Material Issuance?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        document.getElementById('cancelMIReason').value = reason.trim();
                        document.getElementById('cancelMIForm').submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            }
        </script>
        @endif
        <a href="{{ route('material-issuance.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Issuance Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Issuance Number</span>
                        <span class="info-value font-monospace">{{ $materialIssuance->issuance_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $materialIssuance->status === 'issued' ? 'success' : ($materialIssuance->status === 'approved' ? 'primary' : 'warning') }}">
                                {{ ucfirst($materialIssuance->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project</span>
                        <span class="info-value">{{ $materialIssuance->project->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Issuance Type</span>
                        <span class="info-value">
                            <span class="badge badge-info">{{ ucfirst($materialIssuance->issuance_type ?? 'project') }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Work Order Number</span>
                        <span class="info-value font-monospace">{{ $materialIssuance->work_order_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Issuance Date</span>
                        <span class="info-value">{{ $materialIssuance->issuance_date->format('M d, Y') }}</span>
                    </div>
                    @if($materialIssuance->purpose)
                    <div class="info-item full-width">
                        <span class="info-label">Purpose</span>
                        <span class="info-value">{{ $materialIssuance->purpose }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Issued Items</h5>
                <span class="badge badge-info">{{ $materialIssuance->items->count() }} items</span>
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                @if(showPrices())
                                <th>Unit Cost</th>
                                <th>Total</th>
                                @endif
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialIssuance->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->inventoryItem->name }}</div>
                                        <small class="text-muted font-monospace">{{ $item->inventoryItem->item_code ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($item->quantity, 2) }}</span>
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                    @if(showPrices())
                                    <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td><strong class="text-success">₱{{ number_format($item->quantity * $item->unit_cost, 2) }}</strong></td>
                                    @endif
                                    <td><span class="text-muted">{{ $item->notes ?? '—' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if(showPrices())
                        <tfoot>
                            <tr class="table-footer">
                                <th colspan="3" class="text-end">Total Amount:</th>
                                <th colspan="2" class="text-success">
                                    ₱{{ number_format($materialIssuance->items->sum(function($item) { return $item->quantity * $item->unit_cost; }), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="status-card mb-4">
            <div class="status-card-header">
                <h5 class="status-card-title"><i class="bi bi-flag"></i> Status</h5>
            </div>
            <div class="status-card-body">
                <div class="status-indicator">
                    <div class="status-step {{ $materialIssuance->status === 'draft' ? 'active' : ($materialIssuance->status === 'approved' || $materialIssuance->status === 'issued' ? 'completed' : '') }}">
                        <div class="status-step-icon">
                            <i class="bi bi-file-earmark"></i>
                        </div>
                        <div class="status-step-content">
                            <span class="status-step-label">Draft</span>
                            <small class="status-step-desc">Initial creation</small>
                        </div>
                    </div>
                    <div class="status-step {{ $materialIssuance->status === 'approved' ? 'active' : ($materialIssuance->status === 'issued' ? 'completed' : '') }}">
                        <div class="status-step-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="status-step-content">
                            <span class="status-step-label">Approved</span>
                            <small class="status-step-desc">Ready to issue</small>
                        </div>
                    </div>
                    <div class="status-step {{ $materialIssuance->status === 'issued' ? 'active completed' : '' }}">
                        <div class="status-step-icon">
                            <i class="bi bi-box-arrow-up"></i>
                        </div>
                        <div class="status-step-content">
                            <span class="status-step-label">Issued</span>
                            <small class="status-step-desc">Materials issued</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($materialIssuance->notes)
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-sticky"></i> Notes</h5>
            </div>
            <div class="info-card-body">
                <p class="notes-text">{{ $materialIssuance->notes }}</p>
            </div>
        </div>
        @endif
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
    
    .table-modern tfoot th {
        padding: 1.25rem 1rem;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .status-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .status-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    
    .status-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .status-card-body {
        padding: 1.5rem;
    }
    
    .status-indicator {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .status-step {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        opacity: 0.5;
        transition: all 0.3s ease;
    }
    
    .status-step::after {
        content: '';
        position: absolute;
        left: 20px;
        top: 48px;
        width: 2px;
        height: calc(100% + 0.5rem);
        background: #e5e7eb;
    }
    
    .status-step:last-child::after {
        display: none;
    }
    
    .status-step.active,
    .status-step.completed {
        opacity: 1;
    }
    
    .status-step.completed .status-step-icon {
        background: #10b981;
        color: #ffffff;
    }
    
    .status-step.active .status-step-icon {
        background: #2563eb;
        color: #ffffff;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(37, 99, 235, 0);
        }
    }
    
    .status-step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        flex-shrink: 0;
        z-index: 1;
        transition: all 0.3s ease;
    }
    
    .status-step-content {
        flex: 1;
    }
    
    .status-step-label {
        display: block;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }
    
    .status-step-desc {
        display: block;
        color: #6b7280;
        font-size: 0.8125rem;
    }
    
    .notes-text {
        color: #374151;
        line-height: 1.6;
        margin: 0;
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
    
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    
    .modal-header {
        border-radius: 16px 16px 0 0;
        padding: 1.5rem 2rem;
        border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    .modal-title {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        letter-spacing: -0.02em;
    }
    
    .modal-body {
        padding: 2rem;
        font-size: 1.0625rem;
        line-height: 1.7;
    }
    
    .modal-body p {
        font-size: 1.125rem;
        font-weight: 500;
        color: #111827;
        margin-bottom: 1.5rem;
    }
    
    .modal-footer {
        padding: 1.5rem 2rem;
        border-top: 1px solid #e5e7eb;
        gap: 0.75rem;
    }
    
    .modal-footer .btn {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 10px;
    }
    
    .alert {
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        font-size: 1rem;
        line-height: 1.6;
        border: 2px solid;
    }
    
    .alert strong {
        font-size: 1.0625rem;
        font-weight: 700;
    }
    
    .alert ul {
        padding-left: 1.5rem;
        margin-top: 0.75rem;
        margin-bottom: 0;
    }
    
    .alert li {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }
    
    .form-label {
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.75rem;
        font-size: 1.0625rem;
    }
    
    .form-control {
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        padding: 1rem;
        font-size: 1rem;
        transition: all 0.2s ease;
        line-height: 1.5;
    }
    
    .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
        outline: none;
    }
    
    .form-text {
        font-size: 0.9375rem;
        margin-top: 0.5rem;
    }
</style>
@endpush


@endsection
