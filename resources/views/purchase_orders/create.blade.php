@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-cart-check"></i> Create Purchase Order</h1>
        <p class="text-muted mb-0">Create a purchase order from a quotation</p>
    </div>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

@if($quotation)
<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('purchase-orders.store') }}" id="poForm">
            @csrf
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Order Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-check"></i> Expected Delivery Date
                        </label>
                        <input type="date" name="expected_delivery_date" class="form-control-custom @error('expected_delivery_date') is-invalid @enderror" value="{{ old('expected_delivery_date') }}">
                        @error('expected_delivery_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">When the order should be delivered</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-geo-alt"></i> Delivery Address
                        </label>
                        <textarea name="delivery_address" class="form-control-custom textarea-custom @error('delivery_address') is-invalid @enderror" rows="3" placeholder="Enter delivery address">{{ old('delivery_address') }}</textarea>
                        @error('delivery_address')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Where the items should be delivered</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-list-ul"></i> Order Items</span>
                </h5>
                <div class="info-card">
                    <div class="info-card-header">
                        <h5 class="info-card-title"><i class="bi bi-file-earmark-spreadsheet"></i> Items from Quotation</h5>
                        <span class="badge badge-info">{{ $quotation->items->count() }} items</span>
                    </div>
                    <div class="info-card-body">
                        <div class="table-responsive">
                            <table class="table table-modern">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Supplier</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quotation->items as $item)
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
                                            <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                            <td><strong class="text-success">₱{{ number_format($item->total_price, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-footer">
                                        <th colspan="2" class="text-end">Total Amount:</th>
                                        <th colspan="2" class="text-success">
                                            ₱{{ number_format($quotation->total_amount, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-file-text"></i> Terms & Conditions</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-check"></i> Terms & Conditions
                        </label>
                        <textarea name="terms_conditions" class="form-control-custom textarea-custom @error('terms_conditions') is-invalid @enderror" rows="4" placeholder="Enter terms and conditions">{{ old('terms_conditions', $quotation->terms_conditions) }}</textarea>
                        @error('terms_conditions')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Payment terms, delivery conditions, etc.</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Purchase Order
                </button>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-cancel">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@else
<div class="alert alert-info-modern">
    <div class="alert-icon">
        <i class="bi bi-info-circle"></i>
    </div>
    <div class="alert-content">
        <strong>No Quotation Selected</strong>
        <p class="mb-0">Please select a quotation to create a purchase order from.</p>
    </div>
</div>
@endif

@push('styles')
<style>
    .form-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        transition: box-shadow 0.3s ease;
    }
    
    .form-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .form-card-body {
        padding: 2.5rem;
    }
    
    .form-section {
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e5e7eb;
        position: relative;
    }
    
    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-number {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    
    .form-label-custom {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.625rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label-custom i {
        color: #6b7280;
        font-size: 1rem;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.875rem 1rem;
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
    
    .form-control-custom:read-only {
        background: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
    }
    
    .form-control-custom::placeholder {
        color: #9ca3af;
    }
    
    .textarea-custom {
        resize: vertical;
        min-height: 100px;
    }
    
    .invalid-feedback-custom {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #ef4444;
    }
    
    .invalid-feedback-custom i {
        font-size: 1rem;
    }
    
    .form-control-custom.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .form-control-custom.is-invalid:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .form-help-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: #6b7280;
    }
    
    .info-card {
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .info-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .info-card-title {
        font-size: 0.9375rem;
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
    
    .form-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 2px solid #e5e7eb;
    }
    
    .btn-submit {
        padding: 0.875rem 2rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    
    .btn-cancel {
        padding: 0.875rem 1.5rem;
        border-radius: 10px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-cancel:hover {
        background: #f3f4f6;
        transform: translateY(-2px);
    }
    
    .alert-info-modern {
        background: #dbeafe;
        border: 1px solid #2563eb;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: start;
        gap: 1rem;
    }
    
    .alert-icon {
        font-size: 1.5rem;
        color: #2563eb;
        flex-shrink: 0;
    }
    
    .alert-content {
        flex: 1;
    }
    
    .alert-content strong {
        display: block;
        color: #1e40af;
        margin-bottom: 0.5rem;
    }
    
    .alert-content p {
        color: #1e40af;
        margin: 0;
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

@push('scripts')
<script>
    document.getElementById('poForm')?.addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
        
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }, 3000);
    });
</script>
@endpush
@endsection
