@extends('layouts.app')

@section('title', 'New Project')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-folder-plus"></i> Create Project</h1>
        <p class="text-muted mb-0">Add a new construction project to the system</p>
    </div>
    <a href="{{ route('projects.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('projects.store') }}" id="projectForm">
            @csrf
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Basic Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-folder"></i> Project Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., Building Construction - Phase 1" maxlength="255" required>
                            <i class="bi bi-check-circle-fill input-success-icon"></i>
                        </div>
                        @error('name')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Enter a clear and descriptive project name (max 255 characters)</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-calendar"></i> Timeline</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-event"></i> Start Date <span class="text-danger">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="date" name="start_date" class="form-control-custom @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                            <i class="bi bi-calendar3 input-icon-right"></i>
                        </div>
                        @error('start_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Project start date</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-check"></i> End Date <span class="text-danger">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="date" name="end_date" class="form-control-custom @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                            <i class="bi bi-calendar3 input-icon-right"></i>
                        </div>
                        @error('end_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Expected project completion date</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-person-badge"></i> Project Manager</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-person-badge"></i> Project Manager
                        </label>
                        <select name="project_manager_id" class="form-control-custom @error('project_manager_id') is-invalid @enderror">
                            <option value="">Select Project Manager (Optional)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('project_manager_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}@if($user->role) - {{ $user->role->name }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('project_manager_id')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Select the project manager responsible for this project</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">4</span>
                    <span><i class="bi bi-file-text"></i> Additional Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-card-text"></i> Description
                        </label>
                        <textarea name="description" class="form-control-custom textarea-custom" rows="5" placeholder="Describe the project scope, objectives, and key details..." maxlength="2000">{{ old('description') }}</textarea>
                        <small class="form-help-text">Provide a detailed description of the project scope and objectives (max 2000 characters)</small>
                        <div class="char-counter"><span class="char-count">0</span>/2000</div>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-sticky"></i> Notes
                        </label>
                        <textarea name="notes" class="form-control-custom textarea-custom" rows="4" placeholder="Add any important notes, reminders, or special instructions..." maxlength="1000">{{ old('notes') }}</textarea>
                        <small class="form-help-text">Add any important notes, reminders, or special instructions (max 1000 characters)</small>
                        <div class="char-counter"><span class="char-count">0</span>/1000</div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Create Project
                </button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-cancel">
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
    
    .input-wrapper {
        position: relative;
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
        min-height: 120px;
        max-height: 300px;
        font-family: inherit;
        line-height: 1.6;
    }
    
    .char-counter {
        text-align: right;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .char-counter .char-count {
        font-weight: 600;
        color: #374151;
    }
    
    .input-icon-right {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }
    
    .input-success-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #10b981;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    }
    
    .form-control-custom:valid:not(:placeholder-shown) + .input-success-icon,
    .form-control-custom:valid:not(:placeholder-shown) ~ .input-success-icon {
        opacity: 1;
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
    
    .btn-submit:active {
        transform: translateY(0);
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
    
    /* Date input styling */
    input[type="date"] {
        position: relative;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    /* Form validation animation */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .form-control-custom.is-invalid {
        animation: shake 0.3s ease;
    }
    
    .has-error .form-control-custom {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .has-error .input-group-text-custom {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .alert-danger {
        border-radius: 10px;
        border: 1.5px solid #ef4444;
        background: #fef2f2;
        color: #991b1b;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-danger i {
        font-size: 1.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Enhanced form validation
    document.getElementById('projectForm').addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        // Get all required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let firstInvalidField = null;
        
        // Check each required field
        requiredFields.forEach(field => {
            const wrapper = field.closest('.input-wrapper') || field.closest('.input-group-custom');
            
            // Remove previous error styling
            field.classList.remove('is-invalid');
            if (wrapper) {
                wrapper.classList.remove('has-error');
            }
            
            // Check if field is empty
            if (!field.value || field.value.trim() === '') {
                isValid = false;
                field.classList.add('is-invalid');
                if (wrapper) {
                    wrapper.classList.add('has-error');
                }
                
                // Show custom validation message
                if (!field.validationMessage) {
                    field.setCustomValidity('This field is required');
                }
                
                // Store first invalid field for scrolling
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            } else {
                // Clear custom validity if field is filled
                field.setCustomValidity('');
            }
        });
        
        // If form is invalid, prevent submission and show errors
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first invalid field
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidField.focus();
                
                // Show error message
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger alert-dismissible fade show mt-3';
                errorMsg.innerHTML = `
                    <i class="bi bi-exclamation-triangle"></i> Please fill in all required fields before submitting.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                // Remove existing alert if any
                const existingAlert = form.querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                // Insert alert before form actions
                const formActions = form.querySelector('.form-actions');
                formActions.parentNode.insertBefore(errorMsg, formActions);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (errorMsg.parentNode) {
                        errorMsg.remove();
                    }
                }, 5000);
            }
            
            return false;
        }
        
        // If valid, proceed with submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
        
        // Re-enable after 3 seconds if form doesn't submit (fallback)
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }, 3000);
    });
    
    // Real-time validation on blur
    const requiredFields = document.querySelectorAll('#projectForm [required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            const wrapper = this.closest('.input-wrapper') || this.closest('.input-group-custom');
            
            if (!this.value || this.value.trim() === '') {
                this.classList.add('is-invalid');
                if (wrapper) {
                    wrapper.classList.add('has-error');
                }
                if (!this.validationMessage) {
                    this.setCustomValidity('This field is required');
                }
            } else {
                this.classList.remove('is-invalid');
                if (wrapper) {
                    wrapper.classList.remove('has-error');
                }
                this.setCustomValidity('');
            }
        });
        
        // Clear error on input
        field.addEventListener('input', function() {
            if (this.value && this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                const wrapper = this.closest('.input-wrapper') || this.closest('.input-group-custom');
                if (wrapper) {
                    wrapper.classList.remove('has-error');
                }
                this.setCustomValidity('');
            }
        });
    });
    
    // Date validation
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            if (endDate.value && this.value > endDate.value) {
                endDate.setCustomValidity('End date must be after start date');
                endDate.classList.add('is-invalid');
            } else {
                endDate.setCustomValidity('');
                if (endDate.value) {
                    endDate.classList.remove('is-invalid');
                }
            }
        });
        
        endDate.addEventListener('change', function() {
            if (startDate.value && this.value < startDate.value) {
                this.setCustomValidity('End date must be after start date');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                if (this.value) {
                    this.classList.remove('is-invalid');
                }
            }
        });
    }
    
    // Character counter for textareas
    const descriptionField = document.querySelector('textarea[name="description"]');
    const notesField = document.querySelector('textarea[name="notes"]');
    
    function updateCharCounter(textarea, counter) {
        const count = textarea.value.length;
        const maxLength = parseInt(textarea.getAttribute('maxlength'));
        counter.querySelector('.char-count').textContent = count;
        
        if (count > maxLength * 0.9) {
            counter.style.color = '#ef4444';
        } else if (count > maxLength * 0.75) {
            counter.style.color = '#f59e0b';
        } else {
            counter.style.color = '#6b7280';
        }
    }
    
    if (descriptionField) {
        const counter = descriptionField.parentElement.querySelector('.char-counter');
        if (counter) {
            updateCharCounter(descriptionField, counter);
            descriptionField.addEventListener('input', function() {
                updateCharCounter(this, counter);
            });
        }
    }
    
    if (notesField) {
        const counter = notesField.parentElement.querySelector('.char-counter');
        if (counter) {
            updateCharCounter(notesField, counter);
            notesField.addEventListener('input', function() {
                updateCharCounter(this, counter);
            });
        }
    }
</script>
@endpush
@endsection
