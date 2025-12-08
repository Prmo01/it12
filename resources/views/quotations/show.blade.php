@extends('layouts.app')

@section('title', 'Quotation Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-file-earmark-spreadsheet"></i> Quotation</h1>
        <p class="text-muted mb-0">{{ $quotation->quotation_number }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchase-orders.create', ['quotation_id' => $quotation->id]) }}" class="btn btn-success">
            <i class="bi bi-cart-check"></i> Create Purchase Order
        </a>
        @if($quotation->status !== 'rejected' && !$quotation->purchaseOrders()->where('status', '!=', 'cancelled')->exists())
        <form action="{{ route('quotations.cancel', $quotation) }}" method="POST" class="d-inline" id="cancelQuotationForm">
            @csrf
            <input type="hidden" name="cancellation_reason" id="cancelQuotationReason">
            <button type="button" class="btn btn-warning" onclick="cancelQuotation()">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
        </form>
        <script>
            function cancelQuotation() {
                if (confirm('Are you sure you want to cancel this Quotation?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        document.getElementById('cancelQuotationReason').value = reason.trim();
                        document.getElementById('cancelQuotationForm').submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            }
        </script>
        @endif
        <a href="{{ route('quotations.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Quotation Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Quotation Number</span>
                        <span class="info-value font-monospace">{{ $quotation->quotation_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project Code</span>
                        <span class="info-value">
                            @if($quotation->project_code)
                                <span class="badge badge-info font-monospace">{{ $quotation->project_code }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $quotation->status === 'accepted' ? 'success' : ($quotation->status === 'pending' ? 'primary' : 'warning') }}">
                                {{ ucfirst($quotation->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Purchase Request</span>
                        <span class="info-value font-monospace">{{ $quotation->purchaseRequest->pr_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Quotation Date</span>
                        <span class="info-value">{{ $quotation->quotation_date->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Valid Until</span>
                        <span class="info-value">{{ $quotation->valid_until->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Quantity</span>
                        <span class="info-value fw-bold">{{ number_format($quotation->items->sum('quantity'), 2) }} units</span>
                    </div>
                    @if($quotation->terms_conditions)
                    <div class="info-item full-width">
                        <span class="info-label">Terms & Conditions</span>
                        <span class="info-value">{{ $quotation->terms_conditions }}</span>
                    </div>
                    @endif
                    @if($quotation->notes)
                    <div class="info-item full-width">
                        <span class="info-label">Notes</span>
                        <span class="info-value">{{ $quotation->notes }}</span>
                    </div>
                    @endif
                    @if($quotation->status === 'rejected' && $quotation->cancellation_reason)
                    <div class="info-item full-width">
                        <span class="info-label">Cancellation Reason</span>
                        <span class="info-value">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ $quotation->cancellation_reason }}
                            </div>
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Quotation Items</h5>
                <span class="badge badge-info">{{ $quotation->items->count() }} items</span>
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotation->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->inventoryItem->name }}</div>
                                        <small class="text-muted font-monospace">{{ $item->inventoryItem->item_code ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($item->supplier)
                                            <span class="badge badge-info">{{ $item->supplier->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($item->quantity, 2) }}</span>
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-footer">
                                <th class="text-end">Total Quantity:</th>
                                <th>
                                    {{ number_format($quotation->items->sum('quantity'), 2) }} units
                                </th>
                            </tr>
                        </tfoot>
                    </table>
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
                <a href="{{ route('purchase-orders.create', ['quotation_id' => $quotation->id]) }}" class="quick-action-btn">
                    <div class="quick-action-icon bg-success">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="quick-action-content">
                        <span class="quick-action-label">Create Purchase Order</span>
                        <small class="quick-action-desc">Convert this quotation to PO</small>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        
        @if($quotation->valid_until->isPast())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Expired</strong><br>
            This quotation expired on {{ $quotation->valid_until->format('M d, Y') }}
        </div>
        @elseif($quotation->valid_until->diffInDays(now()) <= 7)
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Expiring Soon</strong><br>
            This quotation expires in {{ $quotation->valid_until->diffInDays(now()) }} days
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
    
    .alert {
        padding: 1rem 1.25rem;
        border-radius: 12px;
        border: 1px solid;
        margin-bottom: 1rem;
    }
    
    .alert-warning {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }
    
    .alert-info {
        background: #dbeafe;
        border-color: #2563eb;
        color: #1e40af;
    }
    
    .alert i {
        margin-right: 0.5rem;
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
    
</style>
@endpush


@endsection
