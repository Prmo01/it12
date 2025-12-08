@extends('layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-cart-check"></i> Purchase Order</h1>
        <p class="text-muted mb-0">{{ $purchaseOrder->po_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array($purchaseOrder->status, ['draft', 'pending']))
            <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
            </form>
        @endif
        <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" class="btn btn-secondary">
            <i class="bi bi-printer"></i> Print
        </a>
        @if($purchaseOrder->status !== 'cancelled')
        <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST" class="d-inline" id="cancelPOForm">
            @csrf
            <input type="hidden" name="cancellation_reason" id="cancelPOReason">
            <button type="button" class="btn btn-warning" onclick="cancelPO()">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
        </form>
        <script>
            function cancelPO() {
                if (confirm('Are you sure you want to cancel this Purchase Order?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        document.getElementById('cancelPOReason').value = reason.trim();
                        document.getElementById('cancelPOForm').submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            }
        </script>
        @endif
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Order Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">PO Number</span>
                        <span class="info-value font-monospace">{{ $purchaseOrder->po_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project Code</span>
                        <span class="info-value">
                            @if($purchaseOrder->project_code)
                                <span class="badge badge-info font-monospace">{{ $purchaseOrder->project_code }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $purchaseOrder->status === 'approved' ? 'success' : ($purchaseOrder->status === 'pending' ? 'primary' : ($purchaseOrder->status === 'completed' ? 'info' : 'warning')) }}">
                                {{ ucfirst($purchaseOrder->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">PO Date</span>
                        <span class="info-value">{{ $purchaseOrder->po_date->format('M d, Y') }}</span>
                    </div>
                    @if($purchaseOrder->expected_delivery_date)
                    <div class="info-item">
                        <span class="info-label">Expected Delivery</span>
                        <span class="info-value">{{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="info-label">Total Amount</span>
                        <span class="info-value text-success fw-bold">₱{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>
                    @if($purchaseOrder->delivery_address)
                    <div class="info-item full-width">
                        <span class="info-label">Delivery Address</span>
                        <span class="info-value">{{ $purchaseOrder->delivery_address }}</span>
                    </div>
                    @endif
                    @if($purchaseOrder->terms_conditions)
                    <div class="info-item full-width">
                        <span class="info-label">Terms & Conditions</span>
                        <span class="info-value">{{ $purchaseOrder->terms_conditions }}</span>
                    </div>
                    @endif
                    @if($purchaseOrder->status === 'cancelled' && $purchaseOrder->cancellation_reason)
                    <div class="info-item full-width">
                        <span class="info-label">Cancellation Reason</span>
                        <span class="info-value">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ $purchaseOrder->cancellation_reason }}
                            </div>
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Order Items</h5>
                <span class="badge badge-info">{{ $purchaseOrder->items->count() }} items</span>
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                                @if(showPrices())
                                <th>Unit Price</th>
                                <th>Total</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
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
                                    @if(showPrices())
                                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td><strong class="text-success">₱{{ number_format($item->total_price, 2) }}</strong></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        @if(showPrices())
                        <tfoot>
                            <tr class="table-footer">
                                <th colspan="2" class="text-end">Total Amount:</th>
                                <th colspan="2" class="text-success">
                                    ₱{{ number_format($purchaseOrder->total_amount, 2) }}
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
        <div class="quick-actions-card mb-4">
            <div class="quick-actions-header">
                <h5 class="quick-actions-title"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="quick-actions-body">
                <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" class="quick-action-btn">
                    <div class="quick-action-icon bg-secondary">
                        <i class="bi bi-printer"></i>
                    </div>
                    <div class="quick-action-content">
                        <span class="quick-action-label">Print PO</span>
                        <small class="quick-action-desc">Generate printable version</small>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @if($purchaseOrder->status === 'approved')
                <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="quick-action-btn">
                    <div class="quick-action-icon bg-success">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>
                    <div class="quick-action-content">
                        <span class="quick-action-label">Receive Goods</span>
                        <small class="quick-action-desc">Create goods receipt</small>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @endif
            </div>
        </div>
        
        @if($purchaseOrder->quotation)
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-file-earmark-spreadsheet"></i> Related Quotation</h5>
            </div>
            <div class="info-card-body">
                <div class="related-item">
                    <span class="related-label">Quotation Number</span>
                    <span class="related-value font-monospace">{{ $purchaseOrder->quotation->quotation_number }}</span>
                </div>
                <div class="related-item">
                    <span class="related-label">Quotation Date</span>
                    <span class="related-value">{{ $purchaseOrder->quotation->quotation_date->format('M d, Y') }}</span>
                </div>
                <a href="{{ route('quotations.show', $purchaseOrder->quotation) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                    <i class="bi bi-eye"></i> View Quotation
                </a>
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
    
    .related-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .related-item:last-child {
        margin-bottom: 0;
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
    
    #charCount {
        font-weight: 700;
        color: #374151;
        font-size: 1rem;
    }
    
    .btn-close {
        opacity: 1;
        filter: brightness(0) invert(1);
    }
</style>
@endpush


@endsection
