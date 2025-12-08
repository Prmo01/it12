@extends('layouts.app')

@section('title', 'Goods List')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-boxes"></i> Goods List</h1>
        <p class="text-muted mb-0">Manage inventory items and stock levels</p>
    </div>
    <a href="{{ route('inventory.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Item</a>
</div>

<div class="card goods-list-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr class="{{ $item->needs_reorder ? 'low-stock-row' : '' }}">
                            <td><span class="text-muted">{{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}</span></td>
                            <td><span class="text-muted font-monospace">{{ $item->item_code }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $item->name }}</div>
                                @if($item->description)
                                    <small class="text-muted">{{ Str::limit($item->description, 40) }}</small>
                                @endif
                            </td>
                            <td>{{ $item->category ?? '<span class="text-muted">N/A</span>' }}</td>
                            <td>
                                <span class="type-badge type-{{ str_replace('_', '-', $item->item_type) }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->item_type)) }}
                                </span>
                            </td>
                            <td>
                                <div class="stock-info">
                                    <span class="fw-semibold">{{ number_format($item->current_stock, 2) }}</span>
                                    <span class="text-muted">{{ $item->unit_of_measure }}</span>
                                    @if($item->needs_reorder)
                                        <span class="badge badge-danger ms-2">Low Stock</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('inventory.show', $item) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.edit', $item) }}" class="btn btn-sm btn-action btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-boxes"></i>
                                    <p class="mt-3 mb-0">No items found</p>
                                    <small class="text-muted">Create your first goods item to get started</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .goods-list-card {
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
    
    .low-stock-row {
        background: #fef2f2;
    }
    
    .low-stock-row:hover {
        background: #fee2e2;
    }
    
    .stock-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .type-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .type-raw-material {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .type-finished-good {
        background: #d1fae5;
        color: #065f46;
    }
    
    .type-consumable {
        background: #fef3c7;
        color: #92400e;
    }
    
    .type-tool {
        background: #e9d5ff;
        color: #6b21a8;
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
    
    .btn-edit {
        background: #fef3c7;
        color: #f59e0b;
    }
    
    .btn-edit:hover {
        background: #f59e0b;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
    }
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-secondary {
        background: #6b7280;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-danger {
        background: #ef4444;
        color: #ffffff;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6875rem;
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
@endsection
