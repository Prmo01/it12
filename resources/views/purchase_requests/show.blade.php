@extends('layouts.app')

@section('title', 'Purchase Request Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-file-earmark-text"></i> Purchase Request</h1>
        <p class="text-muted mb-0">{{ $purchaseRequest->pr_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($purchaseRequest->status === 'draft')
            <form method="POST" action="{{ route('purchase-requests.submit', $purchaseRequest) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Submit this purchase request for approval?')">
                    <i class="bi bi-send"></i> Submit for Approval
                </button>
            </form>
        @endif
        @if($purchaseRequest->status === 'submitted')
            <form method="POST" action="{{ route('purchase-requests.approve', $purchaseRequest) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this purchase request? It will be available for quotation creation.')">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
            </form>
        @endif
        @if($purchaseRequest->status === 'draft')
            <form method="POST" action="{{ route('purchase-requests.approve', $purchaseRequest) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this purchase request directly? It will be available for quotation creation.')">
                    <i class="bi bi-check-circle"></i> Approve Directly
                </button>
            </form>
        @endif
        @if($purchaseRequest->status !== 'cancelled' && !$purchaseRequest->quotations()->exists())
        <form action="{{ route('purchase-requests.cancel', $purchaseRequest) }}" method="POST" class="d-inline" id="cancelPRForm">
            @csrf
            <input type="hidden" name="cancellation_reason" id="cancelPRReason">
            <button type="button" class="btn btn-warning" onclick="cancelPR()">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
        </form>
        <script>
            function cancelPR() {
                if (confirm('Are you sure you want to cancel this Purchase Request?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        document.getElementById('cancelPRReason').value = reason.trim();
                        document.getElementById('cancelPRForm').submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            }
        </script>
        @endif
        <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Request Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">PR Number</span>
                        <span class="info-value font-monospace">{{ $purchaseRequest->pr_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $purchaseRequest->status === 'approved' ? 'success' : ($purchaseRequest->status === 'submitted' ? 'primary' : 'warning') }}">
                                {{ ucfirst($purchaseRequest->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project</span>
                        <span class="info-value">{{ $purchaseRequest->project->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Requested By</span>
                        <span class="info-value">{{ $purchaseRequest->requestedBy->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created At</span>
                        <span class="info-value">{{ $purchaseRequest->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($purchaseRequest->purpose)
                    <div class="info-item full-width">
                        <span class="info-label">Purpose</span>
                        <span class="info-value">{{ $purchaseRequest->purpose }}</span>
                    </div>
                    @endif
                    @if($purchaseRequest->notes)
                    <div class="info-item full-width">
                        <span class="info-label">Notes</span>
                        <span class="info-value">{{ $purchaseRequest->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Requested Items</h5>
                <span class="badge badge-info">{{ $purchaseRequest->items->count() }} items</span>
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
                                @if($purchaseRequest->items->where('specifications', '!=', null)->count() > 0)
                                <th>Specifications</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseRequest->items as $item)
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
                                    @if($purchaseRequest->items->where('specifications', '!=', null)->count() > 0)
                                    <td><span class="text-muted">{{ $item->specifications ?? '—' }}</span></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-footer">
                                <th colspan="{{ $purchaseRequest->items->where('specifications', '!=', null)->count() > 0 ? '3' : '2' }}" class="text-end">Total Amount:</th>
                                <th colspan="{{ $purchaseRequest->items->where('specifications', '!=', null)->count() > 0 ? '2' : '2' }}" class="text-success">
                                    ₱{{ number_format($purchaseRequest->items->sum(function($item) { return $item->quantity * $item->unit_cost; }), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($purchaseRequest->quotations->count() > 0)
        <div class="info-card mt-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-file-earmark-spreadsheet"></i> Quotations Received</h5>
                @if($purchaseRequest->quotations->whereIn('status', ['pending', 'accepted'])->count() >= 2)
                <a href="{{ route('quotations.compare', ['purchase_request_id' => $purchaseRequest->id]) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-bar-chart"></i> Compare
                </a>
                @endif
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Quotation #</th>
                                <th>Supplier</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseRequest->quotations as $quotation)
                                <tr>
                                    <td><span class="text-muted font-monospace">{{ $quotation->quotation_number }}</span></td>
                                    <td>{{ $quotation->supplier->name }}</td>
                                    <td><strong>₱{{ number_format($quotation->total_amount, 2) }}</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $quotation->status === 'accepted' ? 'success' : ($quotation->status === 'pending' ? 'primary' : 'secondary') }}">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-sm btn-action btn-view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="quick-actions-card mb-4">
            <div class="quick-actions-header">
                <h5 class="quick-actions-title"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="quick-actions-body">
                <a href="{{ route('quotations.create', ['purchase_request_id' => $purchaseRequest->id]) }}" class="quick-action-btn">
                    <div class="quick-action-icon bg-primary">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </div>
                    <div class="quick-action-content">
                        <span class="quick-action-label">Create Quotation</span>
                        <small class="quick-action-desc">Generate quotation for this PR</small>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @php
                    $quotationCount = $purchaseRequest->quotations()->whereIn('status', ['pending', 'accepted'])->count();
                @endphp
                @if($quotationCount >= 2)
                <a href="{{ route('quotations.compare', ['purchase_request_id' => $purchaseRequest->id]) }}" class="quick-action-btn">
                    <div class="quick-action-icon bg-success">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <div class="quick-action-content">
                        <span class="quick-action-label">Compare Quotations</span>
                        <small class="quick-action-desc">Compare prices from {{ $quotationCount }} suppliers</small>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @endif
            </div>
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
        padding: 0.75rem;
    }
    
    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-btn:last-child {
        margin-bottom: 0;
    }
    
    .quick-action-btn:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }
    
    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .quick-action-content {
        flex: 1;
    }
    
    .quick-action-label {
        display: block;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }
    
    .quick-action-desc {
        display: block;
        color: #6b7280;
        font-size: 0.8125rem;
    }
    
    .quick-action-btn i:last-child {
        color: #9ca3af;
        font-size: 1.125rem;
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
    
    .btn-close {
        opacity: 1;
        filter: brightness(0) invert(1);
    }
</style>
@endpush

@endsection
