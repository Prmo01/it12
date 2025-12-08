@extends('layouts.app')

@section('title', 'Create Goods Issue')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-right"></i> Create Goods Issue</h1>
        <p class="text-muted mb-0">Issue materials for projects and work orders</p>
    </div>
    <a href="{{ route('material-issuance.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('material-issuance.store') }}" id="issuanceForm">
            @csrf
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Issue Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label-custom">
                            <i class="bi bi-briefcase"></i> Project
                        </label>
                        <select name="project_id" class="form-control-custom @error('project_id') is-invalid @enderror">
                            <option value="">Select Project (Optional)</option>
                            @foreach(\App\Models\Project::all() as $proj)
                                <option value="{{ $proj->id }}" {{ (request('project_id') == $proj->id || old('project_id') == $proj->id) ? 'selected' : '' }}>{{ $proj->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Select a project if issuing for a specific project</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-tag"></i> Issuance Type <span class="text-danger">*</span>
                        </label>
                        <select name="issuance_type" class="form-control-custom @error('issuance_type') is-invalid @enderror" required>
                            <option value="project" {{ old('issuance_type', 'project') == 'project' ? 'selected' : '' }}>Project</option>
                            <option value="maintenance" {{ old('issuance_type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="general" {{ old('issuance_type') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="repair" {{ old('issuance_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                            <option value="other" {{ old('issuance_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('issuance_type')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Select the type of issuance</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-text"></i> Work Order Number
                        </label>
                        <input type="text" name="work_order_number" class="form-control-custom @error('work_order_number') is-invalid @enderror" value="{{ old('work_order_number') }}" placeholder="Enter work order number (optional)">
                        @error('work_order_number')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Optional work order or reference number</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-event"></i> Issuance Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="issuance_date" class="form-control-custom @error('issuance_date') is-invalid @enderror" value="{{ old('issuance_date', date('Y-m-d')) }}" required>
                        @error('issuance_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-text"></i> Purpose
                        </label>
                        <input type="text" name="purpose" class="form-control-custom @error('purpose') is-invalid @enderror" value="{{ old('purpose') }}" placeholder="Enter purpose of issuance">
                        @error('purpose')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Optional purpose or reason for issuance</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-list-ul"></i> Items to Issue</span>
                </h5>
                <div id="items-container">
                    <div class="item-row-modern mb-3">
                        <div class="item-row-header">
                            <span class="item-number">Item 1</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label-custom">
                                    <i class="bi bi-box"></i> Item <span class="text-danger">*</span>
                                </label>
                                <select name="items[0][inventory_item_id]" class="form-control-custom item-select" required>
                                    <option value="">Select Item</option>
                                    @foreach(\App\Models\InventoryItem::where('status', 'active')->get() as $item)
                                        <option value="{{ $item->id }}" data-stock="{{ $item->current_stock }}" data-unit="{{ $item->unit_of_measure }}" data-cost="{{ $item->unit_cost }}">{{ $item->name }} ({{ $item->item_code }}) - Stock: {{ number_format($item->current_stock, 2) }} {{ $item->unit_of_measure }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-custom">
                                    <i class="bi bi-123"></i> Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="items[0][quantity]" class="form-control-custom quantity-input" placeholder="0.00" required>
                                <small class="form-help-text stock-info" style="display: none;"></small>
                            </div>
                            @if(showPrices())
                            <div class="col-md-2">
                                <label class="form-label-custom">
                                    <i class="bi bi-cash-stack"></i> Unit Cost (₱)
                                </label>
                                <div class="input-group-custom">
                                    <span class="input-group-text-custom">₱</span>
                                    <input type="number" step="0.01" min="0" name="items[0][unit_cost]" class="form-control-custom" placeholder="0.00">
                                </div>
                            </div>
                            @else
                            <input type="hidden" name="items[0][unit_cost]" value="0">
                            @endif
                            <div class="col-md-2">
                                <label class="form-label-custom">
                                    <i class="bi bi-chat-left-text"></i> Notes
                                </label>
                                <input type="text" name="items[0][notes]" class="form-control-custom" placeholder="Optional notes">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label-custom">&nbsp;</label>
                                <button type="button" class="btn btn-danger w-100 remove-item" title="Remove Item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary" id="add-item">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-sticky"></i> Notes</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-text"></i> Notes
                        </label>
                        <textarea name="notes" class="form-control-custom textarea-custom" rows="3" placeholder="Enter any additional notes about this issuance">{{ old('notes') }}</textarea>
                        <small class="form-help-text">Any additional notes or observations</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Goods Issue
                </button>
                <a href="{{ route('material-issuance.index') }}" class="btn btn-secondary btn-cancel">
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
    
    .input-group-custom {
        display: flex;
        align-items: center;
    }
    
    .input-group-text-custom {
        padding: 0.875rem 0.75rem;
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.9375rem;
    }
    
    .input-group-custom .form-control-custom {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }
    
    .input-group-custom .form-control-custom:focus {
        border-left: 1.5px solid #2563eb;
    }
    
    .stock-info {
        color: #2563eb;
        font-weight: 600;
    }
    
    .stock-info.warning {
        color: #f59e0b;
    }
    
    .stock-info.danger {
        color: #ef4444;
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
    
    .btn-outline-primary {
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .btn-outline-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
    let itemIndex = 1;
    
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const newRow = container.firstElementChild.cloneNode(true);
        
        // Update item number
        const itemNumber = newRow.querySelector('.item-number');
        itemNumber.textContent = `Item ${container.children.length + 1}`;
        
        // Update input names and clear values
        newRow.querySelectorAll('input, select').forEach(el => {
            if (el.name) {
                el.name = el.name.replace('[0]', `[${itemIndex}]`);
            }
            if (el.type !== 'hidden') {
                el.value = '';
                el.classList.remove('is-invalid');
            }
        });
        
        // Clear stock info
        const stockInfo = newRow.querySelector('.stock-info');
        if (stockInfo) {
            stockInfo.style.display = 'none';
            stockInfo.textContent = '';
            stockInfo.classList.remove('warning', 'danger');
        }
        
        // Re-attach event listeners
        attachItemListeners(newRow);
        
        container.appendChild(newRow);
        itemIndex++;
    });
    
    function attachItemListeners(row) {
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        const stockInfo = row.querySelector('.stock-info');
        
        if (itemSelect && quantityInput && stockInfo) {
            itemSelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const stock = parseFloat(option.dataset.stock) || 0;
                const unit = option.dataset.unit || '';
                
                if (this.value) {
                    stockInfo.style.display = 'block';
                    stockInfo.textContent = `Available: ${stock.toFixed(2)} ${unit}`;
                    stockInfo.classList.remove('warning', 'danger');
                    
                    if (stock < 10) {
                        stockInfo.classList.add('warning');
                    }
                    if (stock === 0) {
                        stockInfo.classList.add('danger');
                        stockInfo.textContent = `Out of stock!`;
                    }
                } else {
                    stockInfo.style.display = 'none';
                }
            });
            
            quantityInput.addEventListener('input', function() {
                const option = itemSelect.options[itemSelect.selectedIndex];
                const stock = parseFloat(option.dataset.stock) || 0;
                const quantity = parseFloat(this.value) || 0;
                
                if (itemSelect.value && quantity > stock) {
                    this.classList.add('is-invalid');
                    stockInfo.classList.add('danger');
                    stockInfo.textContent = `Insufficient stock! Available: ${stock.toFixed(2)} ${option.dataset.unit || ''}`;
                } else {
                    this.classList.remove('is-invalid');
                    if (stockInfo.classList.contains('danger')) {
                        stockInfo.classList.remove('danger');
                        stockInfo.textContent = `Available: ${stock.toFixed(2)} ${option.dataset.unit || ''}`;
                    }
                }
            });
        }
    }
    
    // Attach listeners to initial items
    document.querySelectorAll('.item-row-modern').forEach(row => {
        attachItemListeners(row);
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const container = document.getElementById('items-container');
            if (container.children.length > 1) {
                e.target.closest('.item-row-modern').remove();
                // Update item numbers
                container.querySelectorAll('.item-row-modern').forEach((row, index) => {
                    row.querySelector('.item-number').textContent = `Item ${index + 1}`;
                });
            } else {
                alert('At least one item is required.');
            }
        }
    });
    
    // Form validation
    document.getElementById('issuanceForm')?.addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        // Validate at least one item has been selected
        const itemSelects = form.querySelectorAll('.item-select');
        let hasValidItem = false;
        
        itemSelects.forEach(select => {
            if (select.value) {
                hasValidItem = true;
            }
        });
        
        if (!hasValidItem) {
            e.preventDefault();
            alert('Please add at least one item to issue.');
            return false;
        }
        
        // Validate quantities don't exceed stock
        let hasInvalidQuantity = false;
        itemSelects.forEach(select => {
            if (select.value) {
                const row = select.closest('.item-row-modern');
                const quantityInput = row.querySelector('.quantity-input');
                const quantity = parseFloat(quantityInput.value) || 0;
                const option = select.options[select.selectedIndex];
                const stock = parseFloat(option.dataset.stock) || 0;
                
                if (quantity > stock) {
                    hasInvalidQuantity = true;
                    quantityInput.classList.add('is-invalid');
                }
            }
        });
        
        if (hasInvalidQuantity) {
            e.preventDefault();
            alert('One or more items have quantities exceeding available stock.');
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
