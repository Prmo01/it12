@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-truck"></i> Supplier Details</h1>
        <p class="text-muted mb-0">{{ $supplier->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Supplier Information</h5>
            </div>
            <div class="info-card-body">
                <div class="supplier-profile-header mb-4">
                    <div class="supplier-avatar-large">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="supplier-profile-info">
                        <h3 class="supplier-name">{{ $supplier->name }}</h3>
                        <p class="supplier-code font-monospace">{{ $supplier->code }}</p>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Supplier Code</span>
                        <span class="info-value font-monospace">{{ $supplier->code }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($supplier->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Contact Person</span>
                        <span class="info-value">{{ $supplier->contact_person ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value">
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" class="text-decoration-none">
                                    <i class="bi bi-envelope"></i> {{ $supplier->email }}
                                </a>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value">
                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}" class="text-decoration-none">
                                    <i class="bi bi-telephone"></i> {{ $supplier->phone }}
                                </a>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    @if($supplier->address)
                    <div class="info-item full-width">
                        <span class="info-label">Address</span>
                        <span class="info-value">{{ $supplier->address }}</span>
                    </div>
                    @endif
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
                <a href="{{ route('suppliers.edit', $supplier) }}" class="quick-action-item">
                    <div class="quick-action-icon edit-icon">
                        <i class="bi bi-pencil"></i>
                    </div>
                    <div class="quick-action-content">
                        <strong>Edit Supplier</strong>
                        <p class="mb-0">Update supplier information</p>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @if($supplier->email)
                <a href="mailto:{{ $supplier->email }}" class="quick-action-item">
                    <div class="quick-action-icon email-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="quick-action-content">
                        <strong>Send Email</strong>
                        <p class="mb-0">Contact via email</p>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @endif
                @if($supplier->phone)
                <a href="tel:{{ $supplier->phone }}" class="quick-action-item">
                    <div class="quick-action-icon phone-icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div class="quick-action-content">
                        <strong>Call Supplier</strong>
                        <p class="mb-0">Contact via phone</p>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @endif
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-cart-check"></i> Purchase Orders</h5>
            </div>
            <div class="info-card-body">
                <div class="stat-item">
                    <div class="stat-value">{{ $supplier->purchaseOrders->count() ?? 0 }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <a href="{{ route('purchase-orders.index', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                    <i class="bi bi-eye"></i> View Orders
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-box"></i> Supplier Items</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPriceModal">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>
            <div class="info-card-body">
                @if($supplier->prices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Unit</th>
                                    <th>Effective Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplier->prices as $price)
                                    @php
                                        $isExpired = $price->expiry_date && \Carbon\Carbon::parse($price->expiry_date)->isPast();
                                        $isActive = !$isExpired && (!$price->effective_date || \Carbon\Carbon::parse($price->effective_date)->isPast());
                                    @endphp
                                    <tr>
                                        <td><span class="font-monospace">{{ $price->inventoryItem->item_code }}</span></td>
                                        <td>{{ $price->inventoryItem->name }}</td>
                                        <td><span class="text-muted">{{ $price->inventoryItem->unit_of_measure }}</span></td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $price->effective_date ? \Carbon\Carbon::parse($price->effective_date)->format('M d, Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $price->expiry_date ? \Carbon\Carbon::parse($price->expiry_date)->format('M d, Y') : 'No expiry' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($isExpired)
                                                <span class="badge badge-secondary">Expired</span>
                                            @elseif($isActive)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPriceModal{{ $price->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" action="{{ route('suppliers.prices.delete', [$supplier, $price->id]) }}" class="d-inline" onsubmit="return confirm('Delete this item?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-box"></i>
                        <p class="mt-3 mb-0">No items set</p>
                        <small class="text-muted">Add items this supplier can provide</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Price Modal -->
<div class="modal fade" id="addPriceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('suppliers.prices.store', $supplier) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Supplier Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Inventory Item <span class="text-danger">*</span></label>
                        <select name="inventory_item_id" class="form-control" required>
                            <option value="">Select Item</option>
                            @foreach($inventoryItems as $item)
                                <option value="{{ $item->id }}">{{ $item->item_code }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="unit_price" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Price Modals -->
@foreach($supplier->prices as $price)
<div class="modal fade" id="editPriceModal{{ $price->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('suppliers.prices.update', [$supplier, $price->id]) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Supplier Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" value="{{ $price->inventoryItem->item_code }} - {{ $price->inventoryItem->name }}" readonly>
                    </div>
                    <input type="hidden" name="unit_price" value="{{ $price->unit_price ?? 0 }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ $price->effective_date ? \Carbon\Carbon::parse($price->effective_date)->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" value="{{ $price->expiry_date ? \Carbon\Carbon::parse($price->expiry_date)->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes">{{ $price->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<style>
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
        padding: 0.75rem;
    }
    
    .table-modern tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
    }
    
    .empty-state {
        padding: 2rem;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 2.5rem;
        color: #9ca3af;
    }
    
    .empty-state p {
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        margin-top: 0.75rem;
    }
</style>
@endpush

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
    
    .supplier-profile-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .supplier-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 2.5rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .supplier-profile-info {
        flex: 1;
    }
    
    .supplier-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 0.25rem 0;
    }
    
    .supplier-code {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
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
    
    .info-value a {
        color: #2563eb;
        transition: color 0.2s ease;
    }
    
    .info-value a:hover {
        color: #1d4ed8;
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
    
    .quick-action-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-item:last-child {
        margin-bottom: 0;
    }
    
    .quick-action-item:hover {
        background: #f9fafb;
        border-color: #e5e7eb;
        transform: translateX(4px);
    }
    
    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .edit-icon {
        background: #fef3c7;
        color: #d97706;
    }
    
    .email-icon {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .phone-icon {
        background: #d1fae5;
        color: #10b981;
    }
    
    .quick-action-content {
        flex: 1;
    }
    
    .quick-action-content strong {
        display: block;
        color: #111827;
        margin-bottom: 0.25rem;
        font-size: 0.9375rem;
    }
    
    .quick-action-content p {
        color: #6b7280;
        margin: 0;
        font-size: 0.8125rem;
    }
    
    .quick-action-item i.bi-chevron-right {
        color: #9ca3af;
        font-size: 1.25rem;
    }
    
    .stat-item {
        text-align: center;
        padding: 1rem 0;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .badge-warning {
        background: #f59e0b;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush
@endsection
