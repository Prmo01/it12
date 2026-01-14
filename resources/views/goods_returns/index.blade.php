@extends('layouts.app')

@section('title', 'Goods Returns')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-up"></i> Goods Returns</h1>
        <p class="text-muted mb-0">Manage returned goods and inventory adjustments</p>
    </div>
    @if(auth()->user()->isAdmin() || auth()->user()->hasRole('inventory_manager') || auth()->user()->hasRole('warehouse_manager'))
    <a href="{{ route('goods-returns.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Return</a>
    @endif
</div>

<div class="card gr-card">
    <div class="card-body">
        <form method="GET" class="mb-4 filter-form">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" name="search" class="form-control-custom" placeholder="Return Number, GR Number..." value="{{ request('search') }}">
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
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-box-arrow-in-down"></i> Goods Receipt
                    </label>
                    <select name="goods_receipt_id" class="form-control-custom">
                        <option value="">All Goods Receipts</option>
                        @foreach(\App\Models\GoodsReceipt::orderBy('gr_number', 'desc')->get() as $gr)
                            <option value="{{ $gr->id }}" {{ request('goods_receipt_id') == $gr->id ? 'selected' : '' }}>
                                {{ $gr->gr_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'goods_receipt_id']))
                    <a href="{{ route('goods-returns.index') }}" class="btn btn-secondary">
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
                        <th>Return Number</th>
                        <th>Project Code</th>
                        <th>Goods Receipt</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goodsReturns as $return)
                        <tr>
                            <td><span class="text-muted font-monospace">{{ $return->return_number }}</span></td>
                            <td>
                                @if($return->project_code)
                                    <span class="font-monospace" style="color: #000000;">{{ $return->project_code }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold font-monospace">{{ $return->goodsReceipt->gr_number }}</div>
                            </td>
                            <td><span class="text-muted">{{ $return->return_date ? $return->return_date->format('M d, Y') : 'N/A' }}</span></td>
                            <td>
                                <span class="status-text status-text-{{ $return->status === 'approved' ? 'success' : ($return->status === 'pending' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('goods-returns.show', $return) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(($return->status !== 'cancelled' && $return->status !== 'approved') && (auth()->user()->isAdmin() || auth()->user()->hasRole('inventory_manager') || auth()->user()->hasRole('warehouse_manager')))
                                    <form action="{{ route('goods-returns.cancel', $return) }}" method="POST" class="d-inline cancel-form" data-id="{{ $return->id }}">
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
                                    <i class="bi bi-box-arrow-up"></i>
                                    <p class="mt-3 mb-0">No goods returns found</p>
                                    <small class="text-muted">Create your first goods return to get started</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $goodsReturns->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .gr-card {
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('.cancel-form');
                if (confirm('Are you sure you want to cancel this Goods Return?')) {
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
