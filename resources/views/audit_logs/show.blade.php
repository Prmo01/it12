@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-journal-text"></i> Audit Log Details</h1>
        <p class="text-muted mb-0">Detailed information about this system activity</p>
    </div>
    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Log Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Date & Time</span>
                        <span class="info-value">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">User</span>
                        <span class="info-value">
                            @if($auditLog->user)
                                <div class="fw-semibold">{{ $auditLog->user->name }}</div>
                                <small class="text-muted">{{ $auditLog->user->email }}</small>
                                <div class="mt-1">
                                    <span class="badge badge-info">{{ $auditLog->user->role->name ?? 'No Role' }}</span>
                                </div>
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Action</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $auditLog->getActionColor() }}">
                                {{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Model Type</span>
                        <span class="info-value font-monospace">{{ $auditLog->model_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Model ID</span>
                        <span class="info-value font-monospace">{{ $auditLog->model_id }}</span>
                    </div>
                    <div class="info-item full-width">
                        <span class="info-label">Description</span>
                        <span class="info-value">{{ $auditLog->description ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">IP Address</span>
                        <span class="info-value font-monospace">{{ $auditLog->ip_address ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item full-width">
                        <span class="info-label">User Agent</span>
                        <span class="info-value text-muted" style="font-size: 0.875rem;">{{ $auditLog->user_agent ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        @if($auditLog->old_values || $auditLog->new_values)
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-arrow-left-right"></i> Changes</h5>
            </div>
            <div class="info-card-body">
                @if($auditLog->old_values)
                <div class="mb-4">
                    <h6 class="text-muted mb-2">Old Values</h6>
                    <div class="changes-box old-values">
                        <pre class="mb-0">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
                
                @if($auditLog->new_values)
                <div>
                    <h6 class="text-muted mb-2">New Values</h6>
                    <div class="changes-box new-values">
                        <pre class="mb-0">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="info-card">
            <div class="info-card-body text-center py-5">
                <i class="bi bi-info-circle" style="font-size: 3rem; color: #9ca3af;"></i>
                <p class="mt-3 mb-0 text-muted">No change details available</p>
            </div>
        </div>
        @endif
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
    
    .info-item.full-width {
        grid-column: 1 / -1;
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
    
    .changes-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .changes-box.old-values {
        border-left: 4px solid #ef4444;
    }
    
    .changes-box.new-values {
        border-left: 4px solid #10b981;
    }
    
    .changes-box pre {
        font-size: 0.8125rem;
        color: #374151;
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-danger {
        background: #ef4444;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-warning {
        background: #f59e0b;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-info {
        background: #3b82f6;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-secondary {
        background: #6b7280;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush
@endsection

