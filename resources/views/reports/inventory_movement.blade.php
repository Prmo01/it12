@extends('layouts.app')

@section('title', 'Inventory Movement Report')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-in-up"></i> Inventory Movement Report</h1>
        <p class="text-muted mb-0">Track stock movements and inventory history</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.inventory-movement', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.inventory-movement', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card report-card">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.inventory-movement') }}" class="mb-4 filter-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-box"></i> Item
                    </label>
                    <select name="item_id" class="form-control-custom">
                        <option value="">All Items</option>
                        @foreach(\App\Models\InventoryItem::all() as $item)
                            <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom-small">
                        <i class="bi bi-arrow-left-right"></i> Movement Type
                    </label>
                    <select name="movement_type" class="form-control-custom">
                        <option value="">All Types</option>
                        <option value="in" {{ request('movement_type') == 'in' ? 'selected' : '' }}>In</option>
                        <option value="out" {{ request('movement_type') == 'out' ? 'selected' : '' }}>Out</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom-small">
                        <i class="bi bi-calendar-event"></i> Date From
                    </label>
                    <input type="date" name="date_from" class="form-control-custom" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom-small">
                        <i class="bi bi-calendar-check"></i> Date To
                    </label>
                    <input type="date" name="date_to" class="form-control-custom" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['item_id', 'movement_type', 'date_from', 'date_to']))
            <div class="mt-2">
                <a href="{{ route('reports.inventory-movement') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
            @endif
        </form>
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $movement)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $movement->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $movement->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $movement->inventoryItem->name }}</div>
                                <small class="text-muted font-monospace">{{ $movement->inventoryItem->item_code ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ str_contains($movement->movement_type, 'in') ? 'success' : 'danger' }}">
                                    <i class="bi bi-{{ str_contains($movement->movement_type, 'in') ? 'arrow-down' : 'arrow-up' }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold {{ str_contains($movement->movement_type, 'in') ? 'text-success' : 'text-danger' }}">
                                    {{ str_contains($movement->movement_type, 'in') ? '+' : '-' }}{{ number_format($movement->quantity, 2) }}
                                </span>
                                <span class="text-muted">{{ $movement->inventoryItem->unit_of_measure }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ number_format($movement->balance_after, 2) }}</span>
                                <span class="text-muted">{{ $movement->inventoryItem->unit_of_measure }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-box-arrow-in-up"></i>
                                    <p class="mt-3 mb-0">No movements found</p>
                                    <small class="text-muted">Try adjusting your filters</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($data, 'links'))
        <div class="d-flex justify-content-end mt-3">
            {{ $data->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .report-card {
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
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    
    .badge-danger {
        background: #ef4444;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
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
