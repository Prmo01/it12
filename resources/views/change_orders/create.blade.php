@extends('layouts.app')

@section('title', 'Create Add Additional Project')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-plus-circle"></i> Create Add Additional Project</h1>
        <p class="text-muted mb-0">Add additional work to an existing project</p>
    </div>
    <a href="{{ route('change-orders.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('change-orders.store') }}" id="changeOrderForm">
            @csrf
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Project Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-briefcase"></i> Project <span class="text-danger">*</span>
                        </label>
                        <select name="project_id" class="form-control-custom @error('project_id') is-invalid @enderror" required>
                            <option value="">Select Project</option>
                            @foreach(\App\Models\Project::all() as $proj)
                                <option value="{{ $proj->id }}" {{ (request('project_id') == $proj->id || old('project_id') == $proj->id) ? 'selected' : '' }}>{{ $proj->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Select the project to add additional work to</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-file-text"></i> Additional Work Details</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-earmark-text"></i> Description <span class="text-danger">*</span>
                        </label>
                        <textarea name="description" class="form-control-custom textarea-custom @error('description') is-invalid @enderror" rows="4" placeholder="Describe the additional work to be added" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Detailed description of the additional work</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-question-circle"></i> Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" class="form-control-custom textarea-custom @error('reason') is-invalid @enderror" rows="3" placeholder="Explain why this additional work is needed" required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Reason for adding this additional work</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-calendar-event"></i> Schedule & Budget Impact</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-plus"></i> Additional Days <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="additional_days" class="form-control-custom @error('additional_days') is-invalid @enderror" value="{{ old('additional_days', 0) }}" min="0" placeholder="0" required>
                        @error('additional_days')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Number of additional days required</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-cash-stack"></i> Additional Cost (₱) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-custom">
                            <span class="input-group-text-custom">₱</span>
                            <input type="number" step="0.01" min="0" name="additional_cost" class="form-control-custom @error('additional_cost') is-invalid @enderror" value="{{ old('additional_cost', 0) }}" placeholder="0.00" required>
                        </div>
                        @error('additional_cost')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Additional cost for this work</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">4</span>
                    <span><i class="bi bi-sticky"></i> Approval Notes</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-text"></i> Approval Notes
                        </label>
                        <textarea name="approval_notes" class="form-control-custom textarea-custom" rows="3" placeholder="Any notes for approval (optional)">{{ old('approval_notes') }}</textarea>
                        <small class="form-help-text">Optional notes for the approval process</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Add Additional Project
                </button>
                <a href="{{ route('change-orders.index') }}" class="btn btn-secondary btn-cancel">
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
    document.getElementById('changeOrderForm')?.addEventListener('submit', function(e) {
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
