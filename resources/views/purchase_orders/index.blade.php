@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-cart-check"></i> Purchase Orders</h1>
        <p class="text-muted mb-0">Manage and track all purchase orders</p>
    </div>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New PO</a>
</div>

<div class="card po-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Project Code</th>
                        <th>Suppliers</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td><span class="text-muted font-monospace">{{ $po->po_number }}</span></td>
                            <td>
                                @if($po->project_code)
                                    <span class="badge badge-info font-monospace">{{ $po->project_code }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $suppliers = $po->items->pluck('supplier')->filter()->unique('id');
                                @endphp
                                @if($suppliers->count() > 0)
                                    @foreach($suppliers->take(2) as $supplier)
                                        <span class="badge badge-info d-inline-block mb-1">{{ $supplier->name }}</span>
                                    @endforeach
                                    @if($suppliers->count() > 2)
                                        <span class="badge badge-secondary">+{{ $suppliers->count() - 2 }} more</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td><span class="text-muted">{{ $po->po_date->format('M d, Y') }}</span></td>
                            <td><strong>₱{{ number_format($po->total_amount, 2) }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $po->status === 'approved' ? 'success' : ($po->status === 'pending' ? 'primary' : ($po->status === 'completed' ? 'info' : 'warning')) }}">
                                    {{ ucfirst($po->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(in_array($po->status, ['draft', 'pending']))
                                    <form method="POST" action="{{ route('purchase-orders.approve', $po) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this purchase order?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-action btn-approve" title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('purchase-orders.print', $po) }}" class="btn btn-sm btn-action btn-print" title="Print">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @if($po->status !== 'cancelled')
                                    <form action="{{ route('purchase-orders.cancel', $po) }}" method="POST" class="d-inline cancel-form" data-id="{{ $po->id }}">
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
                                    <i class="bi bi-cart-x"></i>
                                    <p class="mt-3 mb-0">No purchase orders found</p>
                                    <small class="text-muted">Create your first purchase order to get started</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $purchaseOrders->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .po-card {
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
    
    .btn-print {
        background: #e5e7eb;
        color: #374151;
    }
    
    .btn-print:hover {
        background: #374151;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(55, 65, 81, 0.3);
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
    
    .btn-approve {
        background: #d1fae5;
        color: #10b981;
    }
    
    .btn-approve:hover {
        background: #10b981;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
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
    
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('.cancel-form');
                if (confirm('Are you sure you want to cancel this Purchase Order?')) {
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
