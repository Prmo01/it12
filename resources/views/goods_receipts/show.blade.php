@extends('layouts.app')

@section('title', 'Goods Receipt Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-in-down"></i> Goods Receipt</h1>
        <p class="text-muted mb-0">{{ $goodsReceipt->gr_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array($goodsReceipt->status, ['draft', 'pending']))
            <form method="POST" action="{{ route('goods-receipts.approve', $goodsReceipt) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Approve & Update Stock
                </button>
            </form>
        @endif
        @if($goodsReceipt->status !== 'cancelled' && $goodsReceipt->status !== 'approved')
        <form action="{{ route('goods-receipts.cancel', $goodsReceipt) }}" method="POST" class="d-inline" id="cancelGRForm">
            @csrf
            <input type="hidden" name="cancellation_reason" id="cancelGRReason">
            <button type="button" class="btn btn-warning" onclick="cancelGR()">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
        </form>
        <script>
            function cancelGR() {
                if (confirm('Are you sure you want to cancel this Goods Receipt?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        document.getElementById('cancelGRReason').value = reason.trim();
                        document.getElementById('cancelGRForm').submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            }
        </script>
        @endif
        <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Receipt Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">GR Number</span>
                        <span class="info-value font-monospace">{{ $goodsReceipt->gr_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project Code</span>
                        <span class="info-value">
                            @if($goodsReceipt->project_code)
                                <span class="badge badge-info font-monospace">{{ $goodsReceipt->project_code }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $goodsReceipt->status === 'approved' ? 'success' : ($goodsReceipt->status === 'pending' ? 'primary' : 'warning') }}">
                                {{ ucfirst($goodsReceipt->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Purchase Order</span>
                        <span class="info-value font-monospace">{{ $goodsReceipt->purchaseOrder->po_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">GR Date</span>
                        <span class="info-value">{{ $goodsReceipt->gr_date->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Received By</span>
                        <span class="info-value">{{ $goodsReceipt->receivedBy->name ?? 'N/A' }}</span>
                    </div>
                    @if($goodsReceipt->delivery_note_number)
                    <div class="info-item">
                        <span class="info-label">Delivery Note</span>
                        <span class="info-value font-monospace">{{ $goodsReceipt->delivery_note_number }}</span>
                    </div>
                    @endif
                    @if($goodsReceipt->remarks)
                    <div class="info-item full-width">
                        <span class="info-label">Remarks</span>
                        <span class="info-value">{{ $goodsReceipt->remarks }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Received Items</h5>
                <span class="badge badge-info">{{ $goodsReceipt->items->count() }} items</span>
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>Ordered</th>
                                <th>Received</th>
                                <th>Accepted</th>
                                <th>Rejected</th>
                                <th>Rejection Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($goodsReceipt->items as $item)
                                <tr class="{{ $item->quantity_rejected > 0 ? 'rejected-row' : '' }}">
                                    <td>
                                        <div class="fw-semibold">{{ $item->inventoryItem->name }}</div>
                                        <small class="text-muted font-monospace">{{ $item->inventoryItem->item_code ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($item->purchaseOrderItem && $item->purchaseOrderItem->supplier)
                                            <span class="badge badge-info">{{ $item->purchaseOrderItem->supplier->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($item->quantity_ordered, 2) }}</span>
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($item->quantity_received, 2) }}</span>
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                    <td>
                                        <span class="text-success fw-semibold">{{ number_format($item->quantity_accepted, 2) }}</span>
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                    <td>
                                        @if($item->quantity_rejected > 0)
                                            <span class="text-danger fw-semibold">{{ number_format($item->quantity_rejected, 2) }}</span>
                                        @else
                                            <span class="text-muted">0.00</span>
                                        @endif
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                    <td><span class="text-muted">{{ $item->rejection_reason ?? '—' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
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
                @if($goodsReceipt->status === 'approved')
                <div class="quick-action-success">
                    <i class="bi bi-check-circle"></i>
                    <div>
                        <strong>Approved</strong>
                        <p class="mb-0">Stock levels have been updated.</p>
                    </div>
                </div>
                @else
                <div class="quick-action-info">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Pending Approval</strong>
                        <p class="mb-0">Approve this receipt to update inventory stock levels.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-cart-check"></i> Related Purchase Order</h5>
            </div>
            <div class="info-card-body">
                <div class="related-item">
                    <span class="related-label">PO Number</span>
                    <span class="related-value font-monospace">{{ $goodsReceipt->purchaseOrder->po_number }}</span>
                </div>
                <div class="related-item">
                    <span class="related-label">PO Date</span>
                    <span class="related-value">{{ $goodsReceipt->purchaseOrder->po_date->format('M d, Y') }}</span>
                </div>
                <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                    <i class="bi bi-eye"></i> View Purchase Order
                </a>
            </div>
        </div>
        
        @if($goodsReceipt->status === 'approved')
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-box-arrow-up"></i> Returns</h5>
            </div>
            <div class="info-card-body">
                <a href="{{ route('goods-returns.create', ['goods_receipt_id' => $goodsReceipt->id]) }}" class="btn btn-sm btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-up"></i> Create Return
                </a>
                <small class="text-muted d-block mt-2">Return items from this receipt</small>
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
    
    .rejected-row {
        background: #fef2f2;
    }
    
    .rejected-row:hover {
        background: #fee2e2;
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
    
</style>
@endpush

@endsection
