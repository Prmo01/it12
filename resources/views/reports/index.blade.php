@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-graph-up"></i> Reports</h1>
        <p class="text-muted mb-0">Generate and view system reports</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-4">
        <div class="report-card">
            <div class="report-card-icon inventory-icon">
                <i class="bi bi-box-arrow-in-up"></i>
            </div>
            <div class="report-card-body">
                <h5 class="report-card-title">Inventory Movement</h5>
                <p class="report-card-text">Track stock movements and inventory history across all items</p>
                <a href="{{ route('reports.inventory-movement') }}" class="btn btn-primary btn-report">
                    <i class="bi bi-eye"></i> View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="report-card">
            <div class="report-card-icon purchase-icon">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="report-card-body">
                <h5 class="report-card-title">Purchase History</h5>
                <p class="report-card-text">View purchase order history and statistics</p>
                <a href="{{ route('reports.purchase-history') }}" class="btn btn-primary btn-report">
                    <i class="bi bi-eye"></i> View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="report-card">
            <div class="report-card-icon supplier-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div class="report-card-body">
                <h5 class="report-card-title">Supplier Performance</h5>
                <p class="report-card-text">Analyze supplier performance metrics and delivery statistics</p>
                <a href="{{ route('reports.supplier-performance') }}" class="btn btn-primary btn-report">
                    <i class="bi bi-eye"></i> View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="report-card">
            <div class="report-card-icon consumption-icon">
                <i class="bi bi-graph-down"></i>
            </div>
            <div class="report-card-body">
                <h5 class="report-card-title">Project Consumption</h5>
                <p class="report-card-text">Track material consumption by project</p>
                <a href="{{ route('reports.project-consumption') }}" class="btn btn-primary btn-report">
                    <i class="bi bi-eye"></i> View Report
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .report-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border-color: #2563eb;
    }
    
    .report-card-icon {
        width: 100%;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }
    
    .report-card-icon::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.1;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="2"/></svg>');
    }
    
    .inventory-icon {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .purchase-icon {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }
    
    .supplier-icon {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .project-icon {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    
    .consumption-icon {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }
    
    .report-card-body {
        padding: 1.75rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .report-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.75rem;
    }
    
    .report-card-text {
        color: #6b7280;
        font-size: 0.9375rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex: 1;
    }
    
    .btn-report {
        width: 100%;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-report:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
</style>
@endpush
@endsection
