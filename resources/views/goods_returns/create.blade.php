@extends('layouts.app')

@section('title', 'Create Goods Return')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-up"></i> Create Goods Return</h1>
        <p class="text-muted mb-0">Return goods from a goods receipt</p>
    </div>
    <a href="{{ route('goods-returns.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

@if($goodsReceipt)
<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('goods-returns.store') }}" id="grForm">
            @csrf
            <input type="hidden" name="goods_receipt_id" value="{{ $goodsReceipt->id }}">
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Return Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-event"></i> Return Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="return_date" class="form-control-custom @error('return_date') is-invalid @enderror" value="{{ old('return_date', date('Y-m-d')) }}" required>
                        @error('return_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label-custom">
                            <i class="bi bi-exclamation-triangle"></i> Reason <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="reason" class="form-control-custom @error('reason') is-invalid @enderror" value="{{ old('reason') }}" placeholder="Enter return reason" required>
                        @error('reason')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">General reason for this return</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-text"></i> Goods Receipt
                        </label>
                        <input type="text" class="form-control-custom" value="{{ $goodsReceipt->gr_number }}" readonly>
                        <small class="form-help-text">Goods receipt being returned</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-list-ul"></i> Items to Return</span>
                </h5>
                <div class="alert alert-info-modern mb-3">
                    <div class="alert-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Select Items to Return</strong>
                        <p class="mb-0">Enter return quantities only for items you want to return. Leave quantity as 0 to skip items. At least one item with quantity greater than 0 is required.</p>
                    </div>
                </div>
                <div id="items-container">
                    @php
                        $itemIndex = 0;
                    @endphp
                    @foreach($goodsReceipt->items as $grItem)
                        @php
                            $returnData = $itemReturnData[$grItem->id] ?? [
                                'accepted' => $grItem->quantity_accepted,
                                'already_returned' => 0,
                                'available' => $grItem->quantity_accepted,
                            ];
                            $available = $returnData['available'];
                        @endphp
                        @if($available > 0)
                        <div class="item-row-modern mb-3">
                            <div class="item-row-header">
                                <span class="item-number">Item {{ $itemIndex + 1 }}</span>
                                <span class="item-badge">Available: {{ number_format($available, 2) }}</span>
                                @if($returnData['already_returned'] > 0)
                                    <span class="item-badge text-warning">Already Returned: {{ number_format($returnData['already_returned'], 2) }}</span>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label-custom">
                                        <i class="bi bi-box"></i> Item
                                    </label>
                                    <input type="text" class="form-control-custom" value="{{ $grItem->inventoryItem->name }} ({{ $grItem->inventoryItem->item_code }})" readonly>
                                    <input type="hidden" name="items[{{ $itemIndex }}][goods_receipt_item_id]" value="{{ $grItem->id }}">
                                    <input type="hidden" name="items[{{ $itemIndex }}][inventory_item_id]" value="{{ $grItem->inventory_item_id }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-custom">
                                        <i class="bi bi-check-circle"></i> Accepted Qty
                                    </label>
                                    <input type="text" class="form-control-custom" value="{{ number_format($grItem->quantity_accepted, 2) }} {{ $grItem->inventoryItem->unit_of_measure }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-custom">
                                        <i class="bi bi-123"></i> Return Qty
                                    </label>
                                    <input type="number" step="0.01" min="0" max="{{ $available }}" name="items[{{ $itemIndex }}][quantity]" class="form-control-custom @error('items.'.$itemIndex.'.quantity') is-invalid @enderror" placeholder="0.00" value="{{ old('items.'.$itemIndex.'.quantity', 0) }}">
                                    @error('items.'.$itemIndex.'.quantity')
                                        <div class="invalid-feedback-custom">
                                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="form-help-text">Leave 0 to skip. Max: {{ number_format($available, 2) }}</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-custom">
                                        <i class="bi bi-chat-left-text"></i> Item Reason
                                    </label>
                                    <input type="text" name="items[{{ $itemIndex }}][reason]" class="form-control-custom" value="{{ old('items.'.$itemIndex.'.reason') }}" placeholder="Reason for this item">
                                </div>
                            </div>
                        </div>
                        @php $itemIndex++; @endphp
                        @endif
                    @endforeach
                    @if($itemIndex === 0)
                        <div class="alert alert-warning-modern">
                            <div class="alert-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="alert-content">
                                <strong>No Items Available for Return</strong>
                                <p class="mb-0">All items from this goods receipt have already been returned.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-sticky"></i> Additional Notes</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-text"></i> Notes
                        </label>
                        <textarea name="notes" class="form-control-custom textarea-custom" rows="3" placeholder="Enter any additional notes">{{ old('notes') }}</textarea>
                        <small class="form-help-text">Any additional information about this return</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Goods Return
                </button>
                <a href="{{ route('goods-returns.index') }}" class="btn btn-secondary btn-cancel">
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
                <strong>Select a Goods Receipt</strong>
                <p class="mb-0">Please select an approved goods receipt to create a return from.</p>
            </div>
        </div>
        
        @if($approvedGoodsReceipts->count() > 0)
        <div class="goods-receipts-list">
            <h5 class="mb-3"><i class="bi bi-list-ul"></i> Approved Goods Receipts</h5>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>GR Number</th>
                            <th>Purchase Order</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedGoodsReceipts as $gr)
                            <tr>
                                <td><span class="font-monospace">{{ $gr->gr_number }}</span></td>
                                <td><span class="font-monospace">{{ $gr->purchaseOrder->po_number }}</span></td>
                                <td>{{ $gr->purchaseOrder->supplier->name }}</td>
                                <td>{{ $gr->gr_date ? $gr->gr_date->format('M d, Y') : 'N/A' }}</td>
                                <td><span class="badge badge-info">{{ $gr->items->count() }} items</span></td>
                                <td>
                                    <a href="{{ route('goods-returns.create', ['goods_receipt_id' => $gr->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-right-circle"></i> Select
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="alert alert-warning-modern">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="alert-content">
                <strong>No Approved Goods Receipts</strong>
                <p class="mb-0">There are no approved goods receipts available for returns. Please approve a goods receipt first.</p>
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
    
    .alert-warning-modern {
        background: #fef3c7;
        border: 1px solid #f59e0b;
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
    
    .alert-warning-modern .alert-icon {
        color: #f59e0b;
    }
    
    .alert-content {
        flex: 1;
    }
    
    .alert-content strong {
        display: block;
        color: #1e40af;
        margin-bottom: 0.5rem;
    }
    
    .alert-warning-modern .alert-content strong {
        color: #92400e;
    }
    
    .alert-content p {
        color: #1e40af;
        margin: 0;
    }
    
    .alert-warning-modern .alert-content p {
        color: #92400e;
    }
    
    .item-badge.text-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('grForm')?.addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        // Validate return quantities
        const quantityInputs = form.querySelectorAll('input[name*="[quantity]"]');
        let isValid = true;
        
        let hasAtLeastOneItem = false;
        
        quantityInputs.forEach(input => {
            const max = parseFloat(input.getAttribute('max'));
            const value = parseFloat(input.value) || 0;
            
            // Only validate items with quantity > 0
            if (value > 0) {
                hasAtLeastOneItem = true;
                
                if (value > max) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    input.setCustomValidity(`Return quantity cannot exceed ${max}`);
                } else if (value < 0) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    input.setCustomValidity('Return quantity cannot be negative');
                } else {
                    input.classList.remove('is-invalid');
                    input.setCustomValidity('');
                }
            } else {
                // Clear validation for items with 0 quantity
                input.classList.remove('is-invalid');
                input.setCustomValidity('');
            }
        });
        
        if (!hasAtLeastOneItem) {
            e.preventDefault();
            alert('Please enter at least one item with a return quantity greater than 0.');
            return false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please check return quantities. They cannot exceed available quantities.');
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
    
    // Real-time validation for quantity inputs
    document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
        input.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('max'));
            const value = parseFloat(this.value) || 0;
            
            // Only validate if value > 0
            if (value > 0) {
                if (value > max) {
                    this.classList.add('is-invalid');
                    this.setCustomValidity(`Cannot exceed ${max}`);
                } else if (value < 0) {
                    this.classList.add('is-invalid');
                    this.setCustomValidity('Cannot be negative');
                } else {
                    this.classList.remove('is-invalid');
                    this.setCustomValidity('');
                }
            } else {
                // Clear validation for 0 or empty values
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
            }
        });
    });
</script>
@endpush
@endsection
