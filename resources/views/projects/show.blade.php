@extends('layouts.app')

@section('title', 'Project')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-folder"></i> {{ $project->name }}</h1>
        <p class="text-muted mb-0">{{ $project->project_code }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($project->status !== 'completed')
        <form action="{{ route('projects.mark-as-done', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to mark this project as done? This will move it to completed projects.');">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Mark as Done
            </button>
        </form>
        @else
        <span class="badge badge-success d-flex align-items-center" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
            <i class="bi bi-check-circle-fill me-2"></i> Project Completed
        </span>
        @endif
        @if($project->status !== 'completed')
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
        @endif
        <a href="{{ $project->status === 'completed' ? route('projects.completed') : route('projects.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Project Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Project Code</span>
                        <span class="info-value font-monospace">{{ $project->project_code }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : ($project->status === 'on_hold' ? 'warning' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project Manager</span>
                        <span class="info-value">
                            @if($project->projectManager)
                                <div class="fw-semibold">{{ $project->projectManager->name }}</div>
                                @if($project->projectManager->role)
                                    <small class="text-muted">{{ $project->projectManager->role->name }}</small>
                                @endif
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Start Date</span>
                        <span class="info-value">{{ $project->start_date->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">End Date</span>
                        <span class="info-value">{{ $project->end_date->format('M d, Y') }}</span>
                    </div>
                    @if($project->actual_end_date)
                    <div class="info-item">
                        <span class="info-label">Actual Completion Date</span>
                        <span class="info-value text-success fw-semibold">
                            <i class="bi bi-check-circle"></i> {{ $project->actual_end_date->format('M d, Y') }}
                        </span>
                    </div>
                    @endif
                    @if(showPrices())
                    <div class="info-item">
                        <span class="info-label">Actual Cost</span>
                        <span class="info-value">₱{{ number_format($project->actual_cost ?? 0, 2) }}</span>
                    </div>
                    @endif
                    <div class="info-item full-width">
                        <span class="info-label">Progress</span>
                        <div class="progress-wrapper">
                            <div class="progress-modern">
                                <div class="progress-bar-modern" style="width: {{ $project->progress_percentage ?? 0 }}%">
                                    <span class="progress-text">{{ $project->progress_percentage ?? 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($project->description)
                    <div class="info-item full-width">
                        <span class="info-label">Description</span>
                        <div class="info-value info-text-content">{{ $project->description }}</div>
                    </div>
                    @endif
                    
                    @if($project->notes)
                    <div class="info-item full-width">
                        <span class="info-label">Notes</span>
                        <div class="info-value info-text-content">{{ $project->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-file-earmark-diff"></i> Change Orders</h5>
                <a href="{{ route('change-orders.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Change Order
                </a>
            </div>
            <div class="info-card-body">
                @if($project->changeOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Description</th>
                                    <th>Additional Days</th>
                                    <th>Additional Cost</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->changeOrders as $co)
                                    <tr>
                                        <td><span class="font-monospace">{{ $co->change_order_number }}</span></td>
                                        <td>{{ Str::limit($co->description, 50) }}</td>
                                        <td>{{ $co->additional_days }} days</td>
                                        <td>₱{{ number_format($co->additional_cost, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $co->status === 'approved' ? 'success' : 'warning' }}">
                                                {{ ucfirst($co->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-x"></i>
                        <p class="mt-3 mb-0">No change orders</p>
                        <small class="text-muted">Add a change order to track project modifications</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-file-earmark-text"></i> Purchase Requests</h5>
                <a href="{{ route('purchase-requests.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> New PR
                </a>
            </div>
            <div class="info-card-body">
                @if($project->purchaseRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>PR Number</th>
                                    <th>Purpose</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->purchaseRequests as $pr)
                                    <tr>
                                        <td><span class="text-muted font-monospace">{{ $pr->pr_number }}</span></td>
                                        <td>{{ Str::limit($pr->purpose, 50) }}</td>
                                        <td>{{ $pr->requestedBy->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $pr->status === 'approved' ? 'success' : ($pr->status === 'submitted' ? 'primary' : 'warning') }}">
                                                {{ ucfirst($pr->status) }}
                                            </span>
                                        </td>
                                        <td><span class="text-muted">{{ $pr->created_at->format('M d, Y') }}</span></td>
                                        <td>
                                            <a href="{{ route('purchase-requests.show', $pr) }}" class="btn btn-sm btn-action btn-view" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-x"></i>
                        <p class="mt-3 mb-0">No purchase requests</p>
                        <small class="text-muted">Create a purchase request to get started</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-file-earmark-check"></i> Quotations</h5>
            </div>
            <div class="info-card-body">
                @php
                    $quotations = $project->purchaseRequests->flatMap->quotations;
                @endphp
                @if($quotations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Quotation Number</th>
                                    <th>PR Number</th>
                                    <th>Supplier</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Quotation Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotations as $quotation)
                                    <tr>
                                        <td><span class="text-muted font-monospace">{{ $quotation->quotation_number }}</span></td>
                                        <td><span class="font-monospace">{{ $quotation->purchaseRequest->pr_number ?? 'N/A' }}</span></td>
                                        <td>{{ $quotation->supplier->name ?? 'N/A' }}</td>
                                        <td><strong class="text-success">₱{{ number_format($quotation->total_amount, 2) }}</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $quotation->status === 'accepted' ? 'success' : ($quotation->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($quotation->status) }}
                                            </span>
                                        </td>
                                        <td><span class="text-muted">{{ $quotation->quotation_date->format('M d, Y') }}</span></td>
                                        <td>
                                            <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-sm btn-action btn-view" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-x"></i>
                        <p class="mt-3 mb-0">No quotations</p>
                        <small class="text-muted">Quotations will appear here once created for purchase requests</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-cart-check"></i> Purchase Orders</h5>
            </div>
            <div class="info-card-body">
                @php
                    $purchaseOrders = $project->purchaseRequests->flatMap->purchaseOrders;
                @endphp
                @if($purchaseOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>PR Number</th>
                                    <th>Supplier</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>PO Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrders as $po)
                                    <tr>
                                        <td><span class="text-muted font-monospace">{{ $po->po_number }}</span></td>
                                        <td><span class="font-monospace">{{ $po->purchaseRequest->pr_number ?? 'N/A' }}</span></td>
                                        <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                                        <td><strong class="text-success">₱{{ number_format($po->total_amount, 2) }}</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $po->status === 'completed' ? 'success' : ($po->status === 'approved' ? 'primary' : ($po->status === 'pending' ? 'warning' : 'secondary')) }}">
                                                {{ ucfirst($po->status) }}
                                            </span>
                                        </td>
                                        <td><span class="text-muted">{{ $po->po_date->format('M d, Y') }}</span></td>
                                        <td>
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-action btn-view" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-x"></i>
                        <p class="mt-3 mb-0">No purchase orders</p>
                        <small class="text-muted">Purchase orders will appear here once created from quotations</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="quick-actions-card mb-4">
            <div class="quick-actions-header">
                <h5 class="quick-actions-title"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="quick-actions-body">
                <a href="{{ route('purchase-requests.create', ['project_id' => $project->id]) }}" class="quick-action-btn">
                    <div class="quick-action-icon bg-primary">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="quick-action-content">
                        <span class="quick-action-label">Create Purchase Request</span>
                        <small class="quick-action-desc">Request materials for this project</small>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('material-issuance.create', ['project_id' => $project->id]) }}" class="quick-action-btn">
                    <div class="quick-action-icon bg-success">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <div class="quick-action-content">
                        <span class="quick-action-label">Issue Goods</span>
                        <small class="quick-action-desc">Issue materials to project</small>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
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
    
    .info-text-content {
        word-wrap: break-word;
        word-break: break-word;
        white-space: pre-wrap;
        line-height: 1.6;
        max-width: 100%;
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-top: 0.5rem;
    }
    
    .progress-wrapper {
        margin-top: 0.5rem;
    }
    
    .progress-modern {
        height: 32px;
        background: #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }
    
    .progress-bar-modern {
        height: 100%;
        background: linear-gradient(90deg, #2563eb, #3b82f6);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.3s ease;
        position: relative;
    }
    
    .progress-text {
        color: #ffffff;
        font-weight: 600;
        font-size: 0.875rem;
        z-index: 1;
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
    
    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-btn:last-child {
        margin-bottom: 0;
    }
    
    .quick-action-btn:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }
    
    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .quick-action-content {
        flex: 1;
    }
    
    .quick-action-label {
        display: block;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }
    
    .quick-action-desc {
        display: block;
        color: #6b7280;
        font-size: 0.8125rem;
    }
    
    .quick-action-btn i:last-child {
        color: #9ca3af;
        font-size: 1.125rem;
    }
    
    .table-modern {
        margin-bottom: 0;
    }
    
    .table-modern thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 0.75rem;
    }
    
    .table-modern tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
    }
    
    .empty-state {
        padding: 2rem;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 2.5rem;
        color: #9ca3af;
    }
    
    .empty-state p {
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        margin-top: 0.75rem;
    }
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-primary {
        background: #2563eb;
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
