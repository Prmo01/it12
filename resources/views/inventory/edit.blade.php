@extends('layouts.app')

@section('title', 'Edit Goods Item')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-pencil"></i> Edit Goods Item</h1>
        <p class="text-muted mb-0">{{ $inventory->name }}</p>
    </div>
    <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('inventory.update', $inventory) }}" id="itemForm">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Basic Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label-custom">
                            <i class="bi bi-tag"></i> Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror" value="{{ old('name', $inventory->name) }}" placeholder="Enter item name" required>
                        @error('name')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-grid"></i> Item Type <span class="text-danger">*</span>
                        </label>
                        <select name="item_type" class="form-control-custom @error('item_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="raw_material" {{ old('item_type', $inventory->item_type) == 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                            <option value="finished_good" {{ old('item_type', $inventory->item_type) == 'finished_good' ? 'selected' : '' }}>Finished Good</option>
                            <option value="consumable" {{ old('item_type', $inventory->item_type) == 'consumable' ? 'selected' : '' }}>Consumable</option>
                            <option value="tool" {{ old('item_type', $inventory->item_type) == 'tool' ? 'selected' : '' }}>Tool</option>
                        </select>
                        @error('item_type')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-rulers"></i> Measurements & Pricing</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-ruler"></i> Unit of Measure <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="unit_of_measure" class="form-control-custom @error('unit_of_measure') is-invalid @enderror" value="{{ old('unit_of_measure', $inventory->unit_of_measure) }}" placeholder="e.g., pcs, kg, m" required>
                        @error('unit_of_measure')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    @if(showPrices())
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-cash-stack"></i> Unit Cost <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-custom">
                            <span class="input-group-text-custom">₱</span>
                            <input type="number" step="0.01" min="0" name="unit_cost" class="form-control-custom @error('unit_cost') is-invalid @enderror" value="{{ old('unit_cost', $inventory->unit_cost) }}" placeholder="0.00" required>
                        </div>
                        @error('unit_cost')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    @else
                    <input type="hidden" name="unit_cost" value="{{ old('unit_cost', $inventory->unit_cost ?? 0) }}">
                    @endif
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-toggle-on"></i> Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" class="form-control-custom @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $inventory->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $inventory->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
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
                            <i class="bi bi-card-text"></i> Description
                        </label>
                        <textarea name="description" class="form-control-custom textarea-custom" rows="3" placeholder="Enter item description">{{ old('description', $inventory->description) }}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Update Item
                </button>
                <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-secondary btn-cancel">
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
    document.getElementById('itemForm').addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
        
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
