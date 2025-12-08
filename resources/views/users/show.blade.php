@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-person"></i> User Details</h1>
        <p class="text-muted mb-0">{{ $user->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> User Information</h5>
            </div>
            <div class="info-card-body">
                <div class="user-profile-header mb-4">
                    <div class="user-avatar-large">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-profile-info">
                        <h3 class="user-name">{{ $user->name }}</h3>
                        <p class="user-email">{{ $user->email }}</p>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value">{{ $user->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value">{{ $user->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role</span>
                        <span class="info-value">
                            <span class="badge badge-role">{{ $user->role->name ?? 'No Role' }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Account Status</span>
                        <span class="info-value">
                            <span class="badge badge-success">Active</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="quick-actions-card mb-4">
            <div class="quick-actions-header">
                <h5 class="quick-actions-title"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="quick-actions-body">
                <a href="{{ route('users.edit', $user) }}" class="quick-action-item">
                    <div class="quick-action-icon edit-icon">
                        <i class="bi bi-pencil"></i>
                    </div>
                    <div class="quick-action-content">
                        <strong>Edit User</strong>
                        <p class="mb-0">Update user information</p>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-shield-check"></i> Permissions</h5>
            </div>
            <div class="info-card-body">
                @if($user->role)
                    <div class="permission-info">
                        <p class="mb-2"><strong>Role:</strong> {{ $user->role->name }}</p>
                        <small class="text-muted">User permissions are defined by their assigned role.</small>
                    </div>
                @else
                    <div class="permission-info">
                        <p class="mb-2 text-muted">No role assigned</p>
                        <small class="text-muted">Assign a role to grant permissions.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .info-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .info-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-card-body {
        padding: 1.5rem;
    }
    
    .user-profile-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .user-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 3rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    
    .user-profile-info {
        flex: 1;
    }
    
    .user-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 0.25rem 0;
    }
    
    .user-email {
        font-size: 1rem;
        color: #6b7280;
        margin: 0;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .info-value {
        font-size: 0.9375rem;
        color: #111827;
        font-weight: 500;
    }
    
    .badge-role {
        background: #2563eb;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .quick-actions-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .quick-actions-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    
    .quick-actions-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .quick-actions-body {
        padding: 0.75rem;
    }
    
    .quick-action-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    
    .quick-action-item:hover {
        background: #f9fafb;
        border-color: #e5e7eb;
        transform: translateX(4px);
    }
    
    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .edit-icon {
        background: #fef3c7;
        color: #d97706;
    }
    
    .quick-action-content {
        flex: 1;
    }
    
    .quick-action-content strong {
        display: block;
        color: #111827;
        margin-bottom: 0.25rem;
        font-size: 0.9375rem;
    }
    
    .quick-action-content p {
        color: #6b7280;
        margin: 0;
        font-size: 0.8125rem;
    }
    
    .quick-action-item i.bi-chevron-right {
        color: #9ca3af;
        font-size: 1.25rem;
    }
    
    .permission-info {
        padding: 0.5rem 0;
    }
    
    .permission-info p {
        color: #374151;
        margin: 0;
    }
    
    .permission-info small {
        color: #6b7280;
    }
</style>
@endpush
@endsection
