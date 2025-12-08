@extends('layouts.app')

@section('title', 'Goods Return Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-up"></i> Goods Return</h1>
        <p class="text-muted mb-0">{{ $goodsReturn->return_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array($goodsReturn->status, ['draft', 'pending']))
            <form method="POST" action="{{ route('goods-returns.approve', $goodsReturn) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Approve & Update Stock
                </button>
            </form>
        @endif
        @if($goodsReturn->status !== 'cancelled' && $goodsReturn->status !== 'approved')
        <form action="{{ route('goods-returns.cancel', $goodsReturn) }}" method="POST" class="d-inline" id="cancelReturnForm">
            @csrf
            <input type="hidden" name="cancellation_reason" id="cancelReturnReason">
            <button type="button" class="btn btn-warning" onclick="cancelReturn()">
                <i class="bi bi-x-circle"></i> Cancel
            </button>
        </form>
        <script>
            function cancelReturn() {
                if (confirm('Are you sure you want to cancel this Goods Return?')) {
                    let reason = prompt('Please provide a reason for cancellation (minimum 10 characters):');
                    if (reason && reason.trim().length >= 10) {
                        document.getElementById('cancelReturnReason').value = reason.trim();
                        document.getElementById('cancelReturnForm').submit();
                    } else if (reason !== null) {
                        alert('Cancellation reason must be at least 10 characters.');
                    }
                }
            }
        </script>
        @endif
        <a href="{{ route('goods-returns.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Return Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Return Number</span>
                        <span class="info-value font-monospace">{{ $goodsReturn->return_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project Code</span>
                        <span class="info-value">
                            @if($goodsReturn->project_code)
                                <span class="badge badge-info font-monospace">{{ $goodsReturn->project_code }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $goodsReturn->status === 'approved' ? 'success' : (in_array($goodsReturn->status, ['draft', 'pending']) ? 'warning' : 'warning') }}">
                                {{ ucfirst($goodsReturn->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Goods Receipt</span>
                        <span class="info-value font-monospace">{{ $goodsReturn->goodsReceipt->gr_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Return Date</span>
                        <span class="info-value">{{ $goodsReturn->return_date ? $goodsReturn->return_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Returned By</span>
                        <span class="info-value">{{ $goodsReturn->returnedBy->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item full-width">
                        <span class="info-label">Reason</span>
                        <span class="info-value">{{ $goodsReturn->reason }}</span>
                    </div>
                    @if($goodsReturn->notes)
                    <div class="info-item full-width">
                        <span class="info-label">Notes</span>
                        <span class="info-value">{{ $goodsReturn->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Returned Items</h5>
                <span class="badge badge-info">{{ $goodsReturn->items->count() }} items</span>
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($goodsReturn->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->inventoryItem->name }}</div>
                                        <small class="text-muted font-monospace">{{ $item->inventoryItem->item_code ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($item->quantity, 2) }}</span>
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                    <td><span class="text-muted">{{ $item->reason ?? '—' }}</span></td>
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
                @if($goodsReturn->status === 'approved')
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
                        <p class="mb-0">Approve this return to update inventory stock levels.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-box-arrow-in-down"></i> Related Goods Receipt</h5>
            </div>
            <div class="info-card-body">
                <div class="related-item">
                    <span class="related-label">GR Number</span>
                    <span class="related-value font-monospace">{{ $goodsReturn->goodsReceipt->gr_number }}</span>
                </div>
                <div class="related-item">
                    <span class="related-label">Receipt Date</span>
                    <span class="related-value">{{ $goodsReturn->goodsReceipt->gr_date ? $goodsReturn->goodsReceipt->gr_date->format('M d, Y') : 'N/A' }}</span>
                </div>
                <a href="{{ route('goods-receipts.show', $goodsReturn->goodsReceipt) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                    <i class="bi bi-eye"></i> View Goods Receipt
                </a>
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
