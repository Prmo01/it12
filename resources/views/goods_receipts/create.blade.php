@extends('layouts.app')

@section('title', 'Create Goods Receipt')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-in-down"></i> Create Goods Receipt</h1>
        <p class="text-muted mb-0">Record received goods from a purchase order</p>
    </div>
    <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

@if($purchaseOrder)
<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('goods-receipts.store') }}" id="grForm">
            @csrf
            <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Receipt Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-event"></i> GR Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="gr_date" class="form-control-custom @error('gr_date') is-invalid @enderror" value="{{ old('gr_date', date('Y-m-d')) }}" required>
                        @error('gr_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-text"></i> Delivery Note Number
                        </label>
                        <input type="text" name="delivery_note_number" class="form-control-custom @error('delivery_note_number') is-invalid @enderror" value="{{ old('delivery_note_number') }}" placeholder="Enter delivery note number">
                        @error('delivery_note_number')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Optional delivery note reference</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-text"></i> Purchase Order
                        </label>
                        <input type="text" class="form-control-custom" value="{{ $purchaseOrder->po_number }}" readonly>
                        <small class="form-help-text">Purchase order being received</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-list-ul"></i> Received Items</span>
                </h5>
                <div id="items-container">
                    @foreach($purchaseOrder->items as $index => $poItem)
                        <div class="item-row-modern mb-3">
                            <div class="item-row-header">
                                <span class="item-number">Item {{ $index + 1 }}</span>
                                <span class="item-badge">Ordered: {{ number_format($poItem->quantity, 2) }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label-custom">
                                        <i class="bi bi-box"></i> Item
                                    </label>
                                    <input type="text" class="form-control-custom" value="{{ $poItem->inventoryItem->name }} ({{ $poItem->inventoryItem->item_code }})" readonly>
                                    <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $poItem->id }}">
                                    <input type="hidden" name="items[{{ $index }}][inventory_item_id]" value="{{ $poItem->inventory_item_id }}">
                                    <input type="hidden" name="items[{{ $index }}][quantity_ordered]" value="{{ $poItem->quantity }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-custom">
                                        <i class="bi bi-123"></i> Received <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][quantity_received]" class="form-control-custom @error('items.'.$index.'.quantity_received') is-invalid @enderror" value="{{ old('items.'.$index.'.quantity_received', $poItem->quantity) }}" placeholder="0.00" required>
                                    @error('items.'.$index.'.quantity_received')
                                        <div class="invalid-feedback-custom">
                                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-custom">
                                        <i class="bi bi-check-circle"></i> Accepted <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][quantity_accepted]" class="form-control-custom @error('items.'.$index.'.quantity_accepted') is-invalid @enderror" value="{{ old('items.'.$index.'.quantity_accepted', $poItem->quantity) }}" placeholder="0.00" required>
                                    @error('items.'.$index.'.quantity_accepted')
                                        <div class="invalid-feedback-custom">
                                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-custom">
                                        <i class="bi bi-x-circle"></i> Rejected
                                    </label>
                                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][quantity_rejected]" class="form-control-custom @error('items.'.$index.'.quantity_rejected') is-invalid @enderror" value="{{ old('items.'.$index.'.quantity_rejected', 0) }}" placeholder="0.00">
                                    @error('items.'.$index.'.quantity_rejected')
                                        <div class="invalid-feedback-custom">
                                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-custom">
                                        <i class="bi bi-chat-left-text"></i> Rejection Reason
                                    </label>
                                    <input type="text" name="items[{{ $index }}][rejection_reason]" class="form-control-custom" value="{{ old('items.'.$index.'.rejection_reason') }}" placeholder="Reason if rejected">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-sticky"></i> Remarks</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-text"></i> Remarks
                        </label>
                        <textarea name="remarks" class="form-control-custom textarea-custom" rows="3" placeholder="Enter any remarks about this receipt">{{ old('remarks') }}</textarea>
                        <small class="form-help-text">Any additional notes or observations</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Goods Receipt
                </button>
                <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary btn-cancel">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@else
<div class="form-card">
    <div class="form-card-body">
        <div class="alert alert-info-modern mb-4">
            <div class="alert-icon">
                <i class="bi bi-info-circle"></i>
            </div>
            <div class="alert-content">
                <strong>Select Purchase Order</strong>
                <p class="mb-0">Please select a purchase order to create a goods receipt from.</p>
            </div>
        </div>

        @if($purchaseOrders && $purchaseOrders->count() > 0)
        <div class="po-selector-section">
            <h5 class="form-section-title mb-3">
                <span class="section-number">1</span>
                <span><i class="bi bi-cart-check"></i> Available Purchase Orders</span>
            </h5>
            <div class="po-list">
                @foreach($purchaseOrders as $po)
                <div class="po-card-item" onclick="selectPurchaseOrder({{ $po->id }})">
                    <div class="po-item-header">
                        <div class="po-item-info">
                            <span class="po-number">{{ $po->po_number }}</span>
                            <span class="po-supplier">{{ $po->supplier->name }}</span>
                        </div>
                        <div class="po-item-actions">
                            <span class="po-date">{{ $po->po_date->format('M d, Y') }}</span>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </div>
                    <div class="po-item-details">
                        <span class="po-amount">₱{{ number_format($po->total_amount, 2) }}</span>
                        <span class="po-items">{{ $po->items->count() }} item(s)</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="alert alert-warning-modern">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="alert-content">
                <strong>No Purchase Orders Available</strong>
                <p class="mb-0">There are no approved purchase orders available for goods receipt. Please ensure you have approved purchase orders first.</p>
            </div>
        </div>
        @endif
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
    
    .item-row-modern {
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.2s ease;
    }
    
    .item-row-modern:hover {
        border-color: #2563eb;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
    }
    
    .item-row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .item-number {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }
    
    .item-badge {
        font-size: 0.75rem;
        color: #6b7280;
        background: #e5e7eb;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
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
    
    .po-selector-section {
        margin-top: 1rem;
    }
    
    .po-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .po-card-item {
        background: #ffffff;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .po-card-item:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        transform: translateY(-2px);
    }
    
    .po-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .po-item-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .po-number {
        font-weight: 700;
        font-size: 1rem;
        color: #111827;
        font-family: 'Courier New', monospace;
    }
    
    .po-supplier {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .po-item-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .po-date {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .po-item-actions i {
        color: #9ca3af;
        font-size: 1.25rem;
    }
    
    .po-item-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .po-amount {
        font-weight: 600;
        font-size: 1.125rem;
        color: #2563eb;
    }
    
    .po-items {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .alert-warning-modern {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: start;
        gap: 1rem;
    }
    
    .alert-warning-modern .alert-icon {
        font-size: 1.5rem;
        color: #f59e0b;
        flex-shrink: 0;
    }
    
    .alert-warning-modern .alert-content strong {
        display: block;
        color: #92400e;
        margin-bottom: 0.5rem;
    }
    
    .alert-warning-modern .alert-content p {
        color: #92400e;
        margin: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('grForm')?.addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        // Validate quantities
        const itemRows = form.querySelectorAll('.item-row-modern');
        let isValid = true;
        
        itemRows.forEach(row => {
            const receivedInput = row.querySelector('input[name*="[quantity_received]"]');
            const acceptedInput = row.querySelector('input[name*="[quantity_accepted]"]');
            const rejectedInput = row.querySelector('input[name*="[quantity_rejected]"]');
            
            const received = parseFloat(receivedInput.value) || 0;
            const accepted = parseFloat(acceptedInput.value) || 0;
            const rejected = parseFloat(rejectedInput.value) || 0;
            
            // Check if accepted + rejected = received
            if (Math.abs(accepted + rejected - received) > 0.01) {
                isValid = false;
                receivedInput.classList.add('is-invalid');
                acceptedInput.classList.add('is-invalid');
                if (rejectedInput.value) {
                    rejectedInput.classList.add('is-invalid');
                }
            } else {
                receivedInput.classList.remove('is-invalid');
                acceptedInput.classList.remove('is-invalid');
                rejectedInput.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Accepted quantity + Rejected quantity must equal Received quantity for each item.');
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
        
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }, 3000);
    });
    
    // Real-time validation
    document.querySelectorAll('input[name*="[quantity_received]"], input[name*="[quantity_accepted]"], input[name*="[quantity_rejected]"]').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('.item-row-modern');
            const receivedInput = row.querySelector('input[name*="[quantity_received]"]');
            const acceptedInput = row.querySelector('input[name*="[quantity_accepted]"]');
            const rejectedInput = row.querySelector('input[name*="[quantity_rejected]"]');
            
            const received = parseFloat(receivedInput.value) || 0;
            const accepted = parseFloat(acceptedInput.value) || 0;
            const rejected = parseFloat(rejectedInput.value) || 0;
            
            if (Math.abs(accepted + rejected - received) > 0.01 && received > 0) {
                receivedInput.classList.add('is-invalid');
                acceptedInput.classList.add('is-invalid');
                if (rejectedInput.value) {
                    rejectedInput.classList.add('is-invalid');
                }
            } else {
                receivedInput.classList.remove('is-invalid');
                acceptedInput.classList.remove('is-invalid');
                rejectedInput.classList.remove('is-invalid');
            }
        });
    });
    
    function selectPurchaseOrder(poId) {
        window.location.href = '{{ route("goods-receipts.create") }}?purchase_order_id=' + poId;
    }
</script>
@endpush
@endsection
