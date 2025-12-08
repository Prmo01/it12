@extends('layouts.app')

@section('title', 'Create Quotation')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-file-earmark-spreadsheet"></i> Create Quotation</h1>
        <p class="text-muted mb-0">Create a quotation from a purchase request</p>
    </div>
    <a href="{{ route('quotations.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('quotations.store') }}" id="quotationForm">
            @csrf
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Quotation Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-text"></i> Purchase Request <span class="text-danger">*</span>
                        </label>
                        <select name="purchase_request_id" class="form-control-custom @error('purchase_request_id') is-invalid @enderror" required id="pr-select">
                            <option value="">Select Purchase Request</option>
                            @foreach(\App\Models\PurchaseRequest::where('status', 'approved')->get() as $pr)
                                <option value="{{ $pr->id }}" {{ (request('purchase_request_id') == $pr->id || old('purchase_request_id') == $pr->id) ? 'selected' : '' }}>
                                    {{ $pr->pr_number }} - {{ $pr->project->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_request_id')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Select an approved purchase request</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-event"></i> Quotation Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="quotation_date" class="form-control-custom @error('quotation_date') is-invalid @enderror" value="{{ old('quotation_date', date('Y-m-d')) }}" required>
                        @error('quotation_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-check"></i> Valid Until <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="valid_until" class="form-control-custom @error('valid_until') is-invalid @enderror" value="{{ old('valid_until') }}" required>
                        @error('valid_until')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Quotation expiration date</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-list-ul"></i> Quotation Items</span>
                </h5>
                <div id="items-container">
                    @if($purchaseRequest)
                        @foreach($purchaseRequest->items as $index => $prItem)
                            <div class="item-row-modern mb-3">
                                <div class="item-row-header">
                                    <span class="item-number">Item {{ $index + 1 }}</span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label-custom">
                                            <i class="bi bi-box"></i> Item
                                        </label>
                                        <input type="text" class="form-control-custom" value="{{ $prItem->inventoryItem->name }} ({{ $prItem->inventoryItem->item_code }})" readonly>
                                        <input type="hidden" name="items[{{ $index }}][inventory_item_id]" value="{{ $prItem->inventory_item_id }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-custom">
                                            <i class="bi bi-truck"></i> Supplier <span class="text-danger">*</span>
                                        </label>
                                        <select name="items[{{ $index }}][supplier_id]" class="form-control-custom item-supplier-select" required>
                                            <option value="">Select Supplier</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label-custom">
                                            <i class="bi bi-123"></i> Quantity
                                        </label>
                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][quantity]" class="form-control-custom" value="{{ $prItem->quantity }}" required>
                                    </div>
                                    {{-- Unit price hidden field - kept for backend processing but not displayed --}}
                                    <input type="hidden" name="items[{{ $index }}][unit_price]" 
                                        id="unit-price-{{ $prItem->inventory_item_id }}" 
                                        class="unit-price-input @error('items.'.$index.'.unit_price') is-invalid @enderror" 
                                        data-item-id="{{ $prItem->inventory_item_id }}"
                                        value="0">
                                    @error('items.'.$index.'.unit_price')
                                        <div class="invalid-feedback-custom">
                                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    <div class="col-md-3">
                                        <label class="form-label-custom">
                                            <i class="bi bi-file-text"></i> Specifications
                                        </label>
                                        <input type="text" name="items[{{ $index }}][specifications]" class="form-control-custom" value="{{ $prItem->specifications }}" placeholder="Item specifications">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state-select">
                            <i class="bi bi-info-circle"></i>
                            <p>Please select a Purchase Request to load items</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-file-text"></i> Additional Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-check"></i> Terms & Conditions
                        </label>
                        <textarea name="terms_conditions" class="form-control-custom textarea-custom @error('terms_conditions') is-invalid @enderror" rows="4" placeholder="Enter terms and conditions">{{ old('terms_conditions') }}</textarea>
                        @error('terms_conditions')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Payment terms, delivery conditions, etc.</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-sticky"></i> Notes
                        </label>
                        <textarea name="notes" class="form-control-custom textarea-custom" rows="3" placeholder="Enter any additional notes">{{ old('notes') }}</textarea>
                        <small class="form-help-text">Any additional information or special instructions</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Quotation
                </button>
                <a href="{{ route('quotations.index') }}" class="btn btn-secondary btn-cancel">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

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
    
    .empty-state-select {
        padding: 3rem;
        text-align: center;
        background: #f9fafb;
        border-radius: 12px;
        border: 2px dashed #e5e7eb;
    }
    
    .empty-state-select i {
        font-size: 2.5rem;
        color: #9ca3af;
        margin-bottom: 1rem;
        display: block;
    }
    
    .empty-state-select p {
        color: #6b7280;
        margin: 0;
        font-weight: 500;
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
</style>
@endpush

@push('scripts')
<script>
    const purchaseRequestId = @json($purchaseRequest ? $purchaseRequest->id : null);
    
    document.getElementById('pr-select').addEventListener('change', function() {
        if (this.value) {
            window.location.href = '{{ route("quotations.create") }}?purchase_request_id=' + this.value;
        }
    });
    
    // Function to load supplier prices
    function loadSupplierPrices() {
        const supplierId = document.getElementById('supplier-select').value;
        const prId = purchaseRequestId || document.getElementById('pr-select').value;
        
        if (!supplierId || !prId) {
            return;
        }
        
        // Prices are loaded in the background (hidden fields)
        
        // Fetch supplier prices
        fetch(`{{ route('api.supplier-prices') }}?supplier_id=${supplierId}&purchase_request_id=${prId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Populate prices (hidden fields for backend processing)
                Object.keys(data).forEach(itemId => {
                    const priceData = data[itemId];
                    const input = document.getElementById(`unit-price-${itemId}`);
                    
                    if (input) {
                        if (priceData.has_price && priceData.unit_price) {
                            // Set the hidden unit price field for backend calculation
                            input.value = parseFloat(priceData.unit_price).toFixed(2);
                        } else {
                            // Default to 0 if no price available
                            input.value = '0';
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching supplier prices:', error);
                // Prices are loaded in background, error is logged but form can still be submitted
            });
    }
    
    // Auto-populate prices when supplier is selected
    document.getElementById('supplier-select').addEventListener('change', function() {
        loadSupplierPrices();
    });
    
    // Also load prices when PR is selected (if supplier is already selected)
    const prSelect = document.getElementById('pr-select');
    if (prSelect) {
        prSelect.addEventListener('change', function() {
            const supplierId = document.getElementById('supplier-select').value;
            if (supplierId && this.value) {
                // Wait a moment for page to reload if redirecting
                setTimeout(() => {
                    if (document.getElementById('supplier-select').value === supplierId) {
                        loadSupplierPrices();
                    }
                }, 100);
            }
        });
    }
    
    // If supplier is already selected on page load, trigger the change event
    document.addEventListener('DOMContentLoaded', function() {
        const supplierSelect = document.getElementById('supplier-select');
        if (supplierSelect && supplierSelect.value && purchaseRequestId) {
            loadSupplierPrices();
        }
    });
    
    // Form validation
    document.getElementById('quotationForm').addEventListener('submit', function(e) {
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
