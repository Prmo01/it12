@extends('layouts.app')

@section('title', 'Compare Quotations')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-bar-chart"></i> Compare Quotations</h1>
        <p class="text-muted mb-0">Compare prices from different suppliers</p>
    </div>
    <div class="d-flex gap-2">
        @if($purchaseRequestId)
        <a href="{{ route('purchase-requests.show', $purchaseRequestId) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to PR
        </a>
        @endif
        <a href="{{ route('quotations.index') }}" class="btn btn-secondary">
            <i class="bi bi-list"></i> All Quotations
        </a>
    </div>
</div>

@php
    $purchaseRequest = \App\Models\PurchaseRequest::with('items.inventoryItem')->find($purchaseRequestId);
    $prItems = $purchaseRequest ? $purchaseRequest->items : collect();
    
    // Get all items from all quotations
    $allItems = collect();
    foreach ($quotations as $quotation) {
        foreach ($quotation->items as $item) {
            $allItems->push([
                'quotation_id' => $quotation->id,
                'inventory_item_id' => $item->inventory_item_id,
                'item' => $item
            ]);
        }
    }
    
    // Get unique inventory items
    $uniqueItems = $prItems->pluck('inventoryItem')->unique('id');
@endphp

@if($quotations->count() < 2)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> 
        You need at least 2 quotations to compare. 
        @if($purchaseRequestId)
        <a href="{{ route('quotations.create', ['purchase_request_id' => $purchaseRequestId]) }}" class="alert-link">Create another quotation</a>
        @endif
    </div>
@endif

<div class="row mb-4">
    @foreach($quotations as $quotation)
    <div class="col-md-{{ 12 / min($quotations->count(), 4) }} mb-3">
        <div class="quotation-card-compare {{ $quotation->status === 'accepted' ? 'accepted' : '' }}">
            <div class="quotation-card-header">
                <div>
                    <h5 class="mb-1">{{ $quotation->supplier->name }}</h5>
                    <small class="text-muted font-monospace">{{ $quotation->quotation_number }}</small>
                </div>
                <div class="text-end">
                    <div class="quotation-total">{{ number_format($quotation->items->sum('quantity'), 2) }} units</div>
                    <span class="badge badge-{{ $quotation->status === 'accepted' ? 'success' : ($quotation->status === 'pending' ? 'primary' : 'secondary') }}">
                        {{ ucfirst($quotation->status) }}
                    </span>
                </div>
            </div>
            <div class="quotation-card-body">
                <div class="quotation-info">
                    <div><strong>Date:</strong> {{ $quotation->quotation_date->format('M d, Y') }}</div>
                    <div><strong>Valid Until:</strong> {{ $quotation->valid_until->format('M d, Y') }}</div>
                    @if($quotation->terms_conditions)
                    <div class="mt-2"><strong>Terms:</strong> {{ Str::limit($quotation->terms_conditions, 100) }}</div>
                    @endif
                </div>
                <div class="quotation-actions mt-3">
                    <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-sm btn-outline-primary w-100 mb-2">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    @if($quotation->status === 'pending')
                    <form method="POST" action="{{ route('quotations.accept', $quotation) }}" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success w-100 mb-2" onclick="return confirm('Accept this quotation? This will reject other pending quotations for this PR.')">
                            <i class="bi bi-check-circle"></i> Accept
                        </button>
                    </form>
                    <form method="POST" action="{{ route('quotations.reject', $quotation) }}" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Reject this quotation?')">
                            <i class="bi bi-x-circle"></i> Reject
                        </button>
                    </form>
                    @elseif($quotation->status === 'accepted')
                    <a href="{{ route('purchase-orders.create', ['quotation_id' => $quotation->id]) }}" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-cart-plus"></i> Create Purchase Order
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="info-card">
    <div class="info-card-header">
        <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Quantity Comparison by Item</h5>
    </div>
    <div class="info-card-body">
        <div class="table-responsive">
            <table class="table table-compare">
                <thead>
                    <tr>
                        <th rowspan="2" class="item-col">Item</th>
                        <th rowspan="2" class="qty-col">Qty</th>
                        @foreach($quotations as $quotation)
                        <th colspan="2" class="supplier-col">
                            <div class="supplier-name">{{ $quotation->supplier->name }}</div>
                            <small class="text-muted">{{ $quotation->quotation_number }}</small>
                        </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($quotations as $quotation)
                        <th class="price-header">Quantity</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($uniqueItems as $inventoryItem)
                    @php
                        $prItem = $prItems->firstWhere('inventory_item_id', $inventoryItem->id);
                        $prQuantity = $prItem ? $prItem->quantity : 0;
                        
                        // Get quantities from each quotation
                        $prices = [];
                        
                        foreach ($quotations as $quotation) {
                            $quoteItem = $quotation->items->firstWhere('inventory_item_id', $inventoryItem->id);
                            if ($quoteItem) {
                                $quantity = $quoteItem->quantity;
                                $prices[$quotation->id] = [
                                    'quantity' => $quantity,
                                    'supplier' => $quotation->supplier->name
                                ];
                            } else {
                                $prices[$quotation->id] = null;
                            }
                        }
                    @endphp
                    <tr>
                        <td class="item-cell">
                            <div class="fw-semibold">{{ $inventoryItem->name }}</div>
                            <small class="text-muted font-monospace">{{ $inventoryItem->item_code ?? '' }}</small>
                        </td>
                        <td class="qty-cell">
                            <span class="fw-semibold">{{ number_format($prQuantity, 2) }}</span>
                            <span class="text-muted">{{ $inventoryItem->unit_of_measure }}</span>
                        </td>
                        @foreach($quotations as $quotation)
                            @if(isset($prices[$quotation->id]) && $prices[$quotation->id])
                                @php
                                    $price = $prices[$quotation->id];
                                @endphp
                                <td class="price-cell">
                                    <strong>{{ number_format($price['quantity'], 2) }}</strong>
                                    <small class="text-muted">{{ $inventoryItem->unit_of_measure }}</small>
                                </td>
                            @else
                                <td class="price-cell text-muted">—</td>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <th colspan="2" class="text-end">Total Quantity:</th>
                        @foreach($quotations as $quotation)
                        <th class="total-cell">
                            <strong>{{ number_format($quotation->items->sum('quantity'), 2) }} units</strong>
                        </th>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    .quotation-card-compare {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 2px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .quotation-card-compare:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .quotation-card-compare.accepted {
        border-color: #10b981;
        background: linear-gradient(to bottom, #ffffff 0%, #f0fdf4 100%);
    }
    
    .quotation-card-header {
        padding: 1.25rem;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .quotation-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: #10b981;
        margin-bottom: 0.5rem;
    }
    
    .quotation-card-body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .quotation-info {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.8;
    }
    
    .info-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-top: 2rem;
    }
    
    .info-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
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
    
    .table-compare {
        margin-bottom: 0;
    }
    
    .table-compare thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        text-align: center;
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }
    
    .item-col {
        width: 25%;
        text-align: left !important;
    }
    
    .qty-col {
        width: 10%;
        text-align: center !important;
    }
    
    .supplier-col {
        text-align: center;
        background: #f3f4f6 !important;
    }
    
    .supplier-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }
    
    .price-header {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
    }
    
    .table-compare tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        text-align: center;
    }
    
    .item-cell {
        text-align: left !important;
    }
    
    .qty-cell {
        text-align: center !important;
    }
    
    .price-cell {
        position: relative;
        font-size: 0.9375rem;
    }
    
    .price-cell.best-price {
        background: #d1fae5;
        font-weight: 600;
    }
    
    .badge-best {
        display: inline-block;
        background: #10b981;
        color: #ffffff;
        font-size: 0.625rem;
        padding: 0.125rem 0.375rem;
        border-radius: 4px;
        margin-left: 0.5rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .total-row {
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
    }
    
    .total-row th {
        padding: 1.25rem 0.75rem;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .total-cell {
        text-align: center !important;
        position: relative;
    }
    
    .total-cell.best-total {
        background: #d1fae5;
    }
    
    .badge-best-total {
        display: block;
        background: #10b981;
        color: #ffffff;
        font-size: 0.625rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        margin-top: 0.25rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .table-compare tbody tr:hover {
        background: #f9fafb;
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
    
    .badge-secondary {
        background: #6b7280;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush
@endsection

