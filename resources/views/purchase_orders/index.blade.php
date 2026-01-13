@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-cart-check"></i> Purchase Orders</h1>
        <p class="text-muted mb-0">
            @if(auth()->user()->hasRole('warehouse_manager'))
                Approved purchase orders ready for goods receipt
            @else
                Manage and track all purchase orders
            @endif
        </p>
        @php
            $pendingCount = \App\Models\PurchaseOrder::whereIn('status', ['draft', 'pending'])->count();
        @endphp
        @if(auth()->user()->isAdmin() && $pendingCount > 0)
        <div class="mt-2">
            <a href="{{ route('purchase-orders.pending') }}" class="badge bg-warning text-dark" style="font-size: 0.875rem; padding: 0.5rem 0.75rem; text-decoration: none;">
                <i class="bi bi-hourglass-split"></i> {{ $pendingCount }} {{ $pendingCount === 1 ? 'PO' : 'POs' }} awaiting approval
            </a>
        </div>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isAdmin() && $pendingCount > 0)
        <a href="{{ route('purchase-orders.pending') }}" class="btn btn-warning">
            <i class="bi bi-hourglass-split"></i> Pending ({{ $pendingCount }})
        </a>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('purchasing'))
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New PO</a>
        @endif
    </div>
</div>

<div class="card po-card">
    <div class="card-body">
        <form method="GET" class="mb-4 filter-form">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" name="search" class="form-control-custom" placeholder="PO Number, Project Code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom-small">
                        <i class="bi bi-funnel"></i> Status
                    </label>
                    <select name="status" class="form-control-custom">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-truck"></i> Supplier
                    </label>
                    <select name="supplier_id" class="form-control-custom">
                        <option value="">All Suppliers</option>
                        @foreach(\App\Models\Supplier::orderBy('name')->get() as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'supplier_id']))
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                    @endif
                </div>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td>
                                @if($po->purchaseRequest && $po->purchaseRequest->project)
                                    <span class="fw-semibold">{{ $po->purchaseRequest->project->name }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td><span class="text-muted font-monospace">{{ $po->po_number }}</span></td>
                            <td><span class="text-muted">{{ $po->po_date->format('M d, Y') }}</span></td>
                            <td>
                                <span class="status-text status-text-{{ $po->status === 'approved' ? 'success' : ($po->status === 'pending' || $po->status === 'draft' ? 'primary' : ($po->status === 'completed' ? 'info' : 'warning')) }}">
                                    {{ ucfirst($po->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @php
                                        $suppliers = $po->items->pluck('supplier')->filter()->unique('id');
                                        $supplierNames = $suppliers->pluck('name')->implode(', ');
                                        $tooltip = 'View';
                                        if ($po->project_code) {
                                            $tooltip .= ' - Project Code: ' . $po->project_code;
                                        }
                                        if ($supplierNames) {
                                            $tooltip .= ' - Suppliers: ' . $supplierNames;
                                        }
                                    @endphp
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-action btn-view" title="{{ $tooltip }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin() && in_array($po->status, ['draft', 'pending']))
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
                                    @if(auth()->user()->hasRole('warehouse_manager') && $po->status === 'approved' && !$po->goodsReceipts()->where('status', 'approved')->exists())
                                    <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $po->id]) }}" class="btn btn-sm btn-action btn-success" title="Create Goods Receipt">
                                        <i class="bi bi-box-arrow-in-down"></i>
                                    </a>
                                    @endif
                                    @if($po->status !== 'cancelled' && (auth()->user()->isAdmin() || auth()->user()->hasRole('purchasing')))
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
    
    .filter-form {
        padding: 1.5rem;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    
    .form-label-custom-small {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label-custom-small i {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        color: #111827;
        background: #ffffff;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        background: #fafbff;
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
