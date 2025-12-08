@extends('layouts.app')

@section('title', 'Create Purchase Request')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-file-earmark-plus"></i> Create Purchase Request</h1>
        <p class="text-muted mb-0">Request materials or items for your project</p>
    </div>
    <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('purchase-requests.store') }}" id="prForm">
            @csrf
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Request Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label-custom">
                            <i class="bi bi-folder"></i> Project
                        </label>
                        <select name="project_id" class="form-control-custom @error('project_id') is-invalid @enderror">
                            <option value="">Select Project (Optional)</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" {{ (request('project_id') == $proj->id || old('project_id') == $proj->id) ? 'selected' : '' }}>
                                    {{ $proj->name }} ({{ $proj->project_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Associate this purchase request with a project (optional)</small>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label-custom">
                            <i class="bi bi-card-text"></i> Purpose
                        </label>
                        <textarea name="purpose" class="form-control-custom textarea-custom @error('purpose') is-invalid @enderror" rows="3" placeholder="Enter the purpose of this purchase request">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Describe why these items are needed</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-list-ul"></i> Requested Items</span>
                </h5>
                <div id="items-container">
                    <div class="item-row-modern mb-3">
                        <div class="item-row-header">
                            <span class="item-number">Item 1</span>
                            <button type="button" class="btn-remove-item" title="Remove item">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label-custom">
                                    <i class="bi bi-box"></i> Item <span class="text-danger">*</span>
                                </label>
                                <select name="items[0][inventory_item_id]" class="form-control-custom @error('items.0.inventory_item_id') is-invalid @enderror" required>
                                    <option value="">Select Item</option>
                                    @foreach(\App\Models\InventoryItem::where('status', 'active')->get() as $item)
                                        <option value="{{ $item->id }}" {{ old('items.0.inventory_item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} ({{ $item->item_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('items.0.inventory_item_id')
                                    <div class="invalid-feedback-custom">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-custom">
                                    <i class="bi bi-123"></i> Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="items[0][quantity]" class="form-control-custom @error('items.0.quantity') is-invalid @enderror" value="{{ old('items.0.quantity') }}" placeholder="0.00" required>
                                @error('items.0.quantity')
                                    <div class="invalid-feedback-custom">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            @if(showPrices())
                            <div class="col-md-2">
                                <label class="form-label-custom">
                                    <i class="bi bi-cash-stack"></i> Unit Cost
                                </label>
                                <div class="input-group-custom">
                                    <span class="input-group-text-custom">₱</span>
                                    <input type="number" step="0.01" min="0" name="items[0][unit_cost]" class="form-control-custom @error('items.0.unit_cost') is-invalid @enderror" value="{{ old('items.0.unit_cost') }}" placeholder="0.00">
                                </div>
                                @error('items.0.unit_cost')
                                    <div class="invalid-feedback-custom">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            @else
                            <input type="hidden" name="items[0][unit_cost]" value="0" required>
                            @endif
                            <div class="col-md-3">
                                <label class="form-label-custom">
                                    <i class="bi bi-file-text"></i> Specifications
                                </label>
                                <input type="text" name="items[0][specifications]" class="form-control-custom @error('items.0.specifications') is-invalid @enderror" value="{{ old('items.0.specifications') }}" placeholder="Item specifications">
                                @error('items.0.specifications')
                                    <div class="invalid-feedback-custom">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-primary btn-add-item" id="add-item">
                    <i class="bi bi-plus-circle"></i> Add Another Item
                </button>
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
                        <small class="form-help-text">Any additional information or special instructions</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Purchase Request
                </button>
                <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary btn-cancel">
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
    
    .form-control-custom::placeholder {
        color: #9ca3af;
    }
    
    .textarea-custom {
        resize: vertical;
        min-height: 100px;
    }
    
    .input-group-custom {
        display: flex;
        align-items: center;
        position: relative;
    }
    
    .input-group-text-custom {
        padding: 0.875rem 0.875rem 0.875rem 1rem;
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #374151;
        font-weight: 700;
        font-size: 1rem;
    }
    
    .input-group-custom .form-control-custom {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }
    
    .input-group-custom .form-control-custom:focus {
        border-left: 1.5px solid #2563eb;
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
    
    .btn-remove-item {
        background: transparent;
        border: none;
        color: #ef4444;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-remove-item:hover {
        background: #fef2f2;
        transform: scale(1.1);
    }
    
    .btn-add-item {
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .btn-add-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
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
    let itemIndex = 1;
    
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const template = container.firstElementChild.cloneNode(true);
        
        // Update item number
        template.querySelector('.item-number').textContent = `Item ${container.children.length + 1}`;
        
        // Update all input names
        template.querySelectorAll('input, select').forEach(el => {
            if (el.name) {
                el.name = el.name.replace(/\[\d+\]/, `[${itemIndex}]`);
                el.value = '';
                el.classList.remove('is-invalid');
            }
        });
        
        // Remove error messages
        template.querySelectorAll('.invalid-feedback-custom').forEach(el => {
            el.remove();
        });
        
        container.appendChild(template);
        itemIndex++;
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-item')) {
            const container = document.getElementById('items-container');
            if (container.children.length > 1) {
                e.target.closest('.item-row-modern').remove();
                
                // Renumber items
                container.querySelectorAll('.item-row-modern').forEach((row, index) => {
                    row.querySelector('.item-number').textContent = `Item ${index + 1}`;
                });
            } else {
                alert('You must have at least one item in the purchase request.');
            }
        }
    });
    
    // Form validation
    document.getElementById('prForm').addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        // Check if at least one item is filled
        const itemSelects = form.querySelectorAll('select[name*="[inventory_item_id]"]');
        let hasValidItem = false;
        
        itemSelects.forEach(select => {
            if (select.value) {
                hasValidItem = true;
            }
        });
        
        if (!hasValidItem) {
            e.preventDefault();
            alert('Please add at least one item to the purchase request.');
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
</script>
@endpush
@endsection
