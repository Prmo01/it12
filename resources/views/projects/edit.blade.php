@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-pencil"></i> Edit Project</h1>
        <p class="text-muted mb-0">{{ $project->name }}</p>
    </div>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('projects.update', $project) }}" id="projectForm" onsubmit="return confirmUpdate()">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h5 class="form-section-title"><i class="bi bi-info-circle"></i> Basic Information</h5>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label-custom">Project Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror" value="{{ old('name', $project->name) }}" placeholder="e.g., Building Construction - Phase 1" maxlength="255" required>
                        @error('name')
                            <div class="invalid-feedback-custom">{{ $message }}</div>
                        @enderror
                        <small class="form-help-text">Clear and descriptive project name (max 255 characters)</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label-custom">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control-custom @error('status') is-invalid @enderror" required>
                            <option value="planning" {{ old('status', $project->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ old('status', $project->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $project->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback-custom">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title"><i class="bi bi-calendar"></i> Timeline</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control-custom @error('start_date') is-invalid @enderror" value="{{ old('start_date', $project->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback-custom">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label-custom">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control-custom @error('end_date') is-invalid @enderror" value="{{ old('end_date', $project->end_date->format('Y-m-d')) }}" required>
                        @error('end_date')
                            <div class="invalid-feedback-custom">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label-custom">Progress %</label>
                        <div class="input-group-custom">
                            <input type="number" min="0" max="100" name="progress_percentage" class="form-control-custom" value="{{ old('progress_percentage', $project->progress_percentage) }}" placeholder="0">
                            <span class="input-group-text-custom">%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(showPrices())
            <div class="form-section">
                <h5 class="form-section-title"><i class="bi bi-cash-stack"></i> Costs</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Actual Cost</label>
                        <div class="input-group-custom">
                            <span class="input-group-text-custom">₱</span>
                            <input type="number" step="0.01" min="0" name="actual_cost" class="form-control-custom" value="{{ old('actual_cost', $project->actual_cost) }}" placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>
            @else
            <input type="hidden" name="actual_cost" value="{{ old('actual_cost', $project->actual_cost ?? 0) }}">
            @endif
            
            <div class="form-section">
                <h5 class="form-section-title"><i class="bi bi-person-badge"></i> Project Manager</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">Project Manager</label>
                        <select name="project_manager_id" class="form-control-custom @error('project_manager_id') is-invalid @enderror">
                            <option value="">Select Project Manager (Optional)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('project_manager_id', $project->project_manager_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}@if($user->role) - {{ $user->role->name }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('project_manager_id')
                            <div class="invalid-feedback-custom">{{ $message }}</div>
                        @enderror
                        <small class="form-help-text">Select the project manager responsible for this project</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title"><i class="bi bi-file-text"></i> Additional Information</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">Description</label>
                        <textarea name="description" class="form-control-custom textarea-custom" rows="5" placeholder="Describe the project scope, objectives, and key details..." maxlength="2000">{{ old('description', $project->description) }}</textarea>
                        <small class="form-help-text">Project scope and objectives (max 2000 characters)</small>
                        <div class="char-counter"><span class="char-count">{{ strlen(old('description', $project->description ?? '')) }}</span>/2000</div>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label-custom">Notes</label>
                        <textarea name="notes" class="form-control-custom textarea-custom" rows="4" placeholder="Add any important notes, reminders, or special instructions..." maxlength="1000">{{ old('notes', $project->notes) }}</textarea>
                        <small class="form-help-text">Important notes and reminders (max 1000 characters)</small>
                        <div class="char-counter"><span class="char-count">{{ strlen(old('notes', $project->notes ?? '')) }}</span>/1000</div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Update Project
                </button>
                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Cancel</a>
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
    }
    
    .form-card-body {
        padding: 2rem;
    }
    
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .form-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label-custom {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.75rem 1rem;
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
    }
    
    .form-control-custom::placeholder {
        color: #9ca3af;
    }
    
    .input-group-custom {
        display: flex;
        align-items: center;
    }
    
    .input-group-text-custom {
        padding: 0.75rem 0.75rem 0.75rem 1rem;
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #6b7280;
        font-weight: 600;
    }
    
    .input-group-custom .form-control-custom {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }
    
    .input-group-custom .form-control-custom:focus {
        border-left: 1.5px solid #2563eb;
    }
    
    .invalid-feedback-custom {
        display: block;
        width: 100%;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #ef4444;
    }
    
    .form-control-custom.is-invalid {
        border-color: #ef4444;
    }
    
    .form-control-custom.is-invalid:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .form-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn-submit {
        padding: 0.75rem 1.5rem;
        font-weight: 600;
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
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .form-control-custom.is-invalid {
        animation: shake 0.3s ease;
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
    
    .form-help-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: #6b7280;
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
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
        
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
    
    // Store original status for comparison
    const originalStatus = '{{ $project->status }}';
    
    // Confirmation function for form submission
    window.confirmUpdate = function() {
        const currentStatus = document.querySelector('select[name="status"]').value;
        let message = 'Are you sure you want to update this project?';
        
        // If status is being changed, add specific message
        if (currentStatus !== originalStatus) {
            const statusNames = {
                'planning': 'Planning',
                'active': 'Active',
                'on_hold': 'On Hold',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            message = `Are you sure you want to update this project?\n\nStatus will be changed from "${statusNames[originalStatus]}" to "${statusNames[currentStatus]}".`;
            
            // Special message if changing to completed
            if (currentStatus === 'completed') {
                message += '\n\nNote: This will move the project to completed projects.';
            }
        }
        
        return confirm(message);
    };
</script>
@endpush
@endsection
