@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-person-gear"></i> Edit User</h1>
        <p class="text-muted mb-0">Update user information and permissions</p>
    </div>
    <a href="{{ route('users.show', $user) }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('users.update', $user) }}" id="userForm">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-person"></i> Personal Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label-custom">
                            <i class="bi bi-person"></i> Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" placeholder="Enter full name" required>
                        @error('name')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Enter the user's full name</small>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label-custom">
                            <i class="bi bi-envelope"></i> Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" class="form-control-custom @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" placeholder="Enter email address" required>
                        @error('email')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Unique email address for login</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-shield-lock"></i> Security</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-lock"></i> New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" name="password" id="password" class="form-control-custom @error('password') is-invalid @enderror" placeholder="Leave blank to keep current password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="bi bi-eye" id="password-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-help-text">Leave blank to keep current password</small>
                        <div class="password-requirements mt-2" id="passwordRequirements" style="display: none;">
                            <small class="form-help-text d-block mb-2"><strong>Password Requirements:</strong></small>
                            <ul class="password-requirements-list">
                                <li class="requirement-item" id="req-length">
                                    <i class="bi bi-circle"></i> <span>At least 12 characters</span>
                                </li>
                                <li class="requirement-item" id="req-uppercase">
                                    <i class="bi bi-circle"></i> <span>One uppercase letter</span>
                                </li>
                                <li class="requirement-item" id="req-lowercase">
                                    <i class="bi bi-circle"></i> <span>One lowercase letter</span>
                                </li>
                                <li class="requirement-item" id="req-number">
                                    <i class="bi bi-circle"></i> <span>One number</span>
                                </li>
                                <li class="requirement-item" id="req-symbol">
                                    <i class="bi bi-circle"></i> <span>One symbol</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-lock-fill"></i> Confirm New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control-custom" placeholder="Confirm new password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="bi bi-eye" id="password_confirmation-icon"></i>
                            </button>
                        </div>
                        <small class="form-help-text">Re-enter the password to confirm</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-person-badge"></i> Role & Permissions</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-shield-check"></i> Role & Permissions <span class="text-danger">*</span>
                        </label>
                        <select name="role_id" id="role_id" class="form-control-custom @error('role_id') is-invalid @enderror" required>
                            <option value="">Select a Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" 
                                    data-description="{{ $role->description }}"
                                    {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <div id="role-description" class="role-description mt-2" style="display: none;">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Role Description:</strong>
                                <p class="mb-0 mt-1" id="role-description-text"></p>
                            </div>
                        </div>
                        <small class="form-help-text">Select a role to define what modules and actions this user can access</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Update User
                </button>
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-cancel">
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
    
    .password-input-wrapper {
        position: relative;
    }
    
    .password-input-wrapper .form-control-custom {
        padding-right: 3rem;
    }
    
    .password-toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
    }
    
    .password-toggle:hover {
        color: #2563eb;
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
    
    .password-requirements {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 0.5rem;
    }
    
    .password-requirements-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .requirement-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
        color: #6b7280;
        transition: color 0.2s ease;
    }
    
    .requirement-item i {
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }
    
    .requirement-item.valid {
        color: #10b981;
    }
    
    .requirement-item.valid i {
        color: #10b981;
    }
    
    
    .form-control-custom.is-valid {
        border-color: #10b981;
        background: #f0fdf4;
    }
    
    .form-control-custom.is-valid:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
    
    .role-description {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .role-description .alert {
        border-left: 4px solid #3b82f6;
        background: #eff6ff;
        border-color: #bfdbfe;
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
    
    function validatePassword(password) {
        const requirements = {
            length: password.length >= 12,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };
        
        // Update requirement indicators
        const updateRequirement = (id, isValid) => {
            const item = document.getElementById(id);
            if (item) {
                const icon = item.querySelector('i');
                if (isValid) {
                    item.classList.add('valid');
                    icon.classList.remove('bi-circle');
                    icon.classList.add('bi-check-circle');
                } else {
                    item.classList.remove('valid');
                    icon.classList.remove('bi-check-circle');
                    icon.classList.add('bi-circle');
                }
            }
        };
        
        const reqContainer = document.getElementById('passwordRequirements');
        if (reqContainer) {
            updateRequirement('req-length', requirements.length);
            updateRequirement('req-uppercase', requirements.uppercase);
            updateRequirement('req-lowercase', requirements.lowercase);
            updateRequirement('req-number', requirements.number);
            updateRequirement('req-symbol', requirements.symbol);
        }
        
        return Object.values(requirements).every(req => req === true);
    }
    
    document.getElementById('password')?.addEventListener('input', function() {
        const password = this.value;
        const reqContainer = document.getElementById('passwordRequirements');
        
        if (password.length > 0) {
            reqContainer.style.display = 'block';
            const isValid = validatePassword(password);
            
            if (isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            reqContainer.style.display = 'none';
            this.classList.remove('is-invalid', 'is-valid');
            // Reset all requirements
            if (reqContainer) {
                document.querySelectorAll('.requirement-item').forEach(item => {
                    item.classList.remove('valid');
                    const icon = item.querySelector('i');
                    icon.classList.remove('bi-check-circle');
                    icon.classList.add('bi-circle');
                });
            }
        }
    });
    
    document.getElementById('password_confirmation')?.addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmation = this.value;
        
        if (confirmation.length > 0) {
            if (password === confirmation && password.length > 0) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            this.classList.remove('is-invalid', 'is-valid');
        }
    });
    
    // Role description display
    const roleSelect = document.getElementById('role_id');
    const roleDescription = document.getElementById('role-description');
    const roleDescriptionText = document.getElementById('role-description-text');
    
    if (roleSelect) {
        // Show description on page load if role is already selected
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const description = selectedOption.getAttribute('data-description');
            if (description) {
                roleDescriptionText.textContent = description;
                roleDescription.style.display = 'block';
            }
        }
        
        roleSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const description = selectedOption.getAttribute('data-description');
                if (description) {
                    roleDescriptionText.textContent = description;
                    roleDescription.style.display = 'block';
                } else {
                    roleDescription.style.display = 'none';
                }
            } else {
                roleDescription.style.display = 'none';
            }
        });
    }
    
    document.getElementById('userForm')?.addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;
        
        // Validate role selection
        const roleId = document.getElementById('role_id').value;
        if (!roleId) {
            e.preventDefault();
            alert('Please select a role for the user.');
            document.getElementById('role_id').focus();
            return false;
        }
        
        // Only validate if password fields are filled
        if (password || passwordConfirmation) {
            // Validate password strength if password is provided
            if (password.length > 0 && !validatePassword(password)) {
                e.preventDefault();
                alert('Password does not meet all requirements. Please ensure your password has at least 12 characters, including uppercase, lowercase, numbers, and symbols.');
                document.getElementById('password').focus();
                return false;
            }
            
            if (password !== passwordConfirmation) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
                document.getElementById('password_confirmation').classList.add('is-invalid');
                return false;
            }
        }
        
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
