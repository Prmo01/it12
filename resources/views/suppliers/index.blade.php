@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-truck"></i> Suppliers</h1>
        <p class="text-muted mb-0">Manage supplier information and contacts</p>
    </div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Supplier</a>
</div>

<div class="card suppliers-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $supplier)
                        <tr>
                            <td><span class="text-muted">{{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $index + 1 }}</span></td>
                            <td><span class="text-muted font-monospace">{{ $supplier->code }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $supplier->name }}</div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $supplier->contact_person ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="text-decoration-none">
                                        <i class="bi bi-envelope"></i> {{ $supplier->email }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($supplier->phone)
                                    <a href="tel:{{ $supplier->phone }}" class="text-decoration-none">
                                        <i class="bi bi-telephone"></i> {{ $supplier->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($supplier->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-action btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-truck"></i>
                                    <p class="mt-3 mb-0">No suppliers found</p>
                                    <small class="text-muted">Create your first supplier to get started</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $suppliers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .suppliers-card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
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
        padding: 1rem;
    }
    
    .table-modern tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
        transform: scale(1.001);
    }
    
    .badge-success {
        background: #10b981;
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
    
    .btn-action {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-view {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .btn-view:hover {
        background: #2563eb;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    }
    
    .btn-edit {
        background: #fef3c7;
        color: #d97706;
    }
    
    .btn-edit:hover {
        background: #d97706;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(217, 119, 6, 0.3);
    }
    
    .empty-state {
        padding: 2rem;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #9ca3af;
    }
    
    .empty-state p {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
    }
</style>
@endpush
@endsection
