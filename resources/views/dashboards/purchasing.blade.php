@extends('layouts.app')

@section('title', 'Purchasing Dashboard')

@php
    $user = auth()->user();
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-speedometer2"></i> Purchasing Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, {{ $user->name ?? 'User' }}! Manage procurement and supplier relations.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-2">
    <div class="col-md-3 mb-3">
        <a href="{{ route('purchase-orders.pending') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-warning" style="cursor: pointer;">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Pending POs</p>
                            <h2 class="stat-value">{{ $pendingPOs }}</h2>
                            <small class="stat-change text-muted">Awaiting approval</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('purchase-requests.index') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-primary" style="cursor: pointer;">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Pending PRs</p>
                            <h2 class="stat-value">{{ $pendingPRs }}</h2>
                            <small class="stat-change text-muted">Purchase requests</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('suppliers.index') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-success" style="cursor: pointer;">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Total Suppliers</p>
                            <h2 class="stat-value">{{ $totalSuppliers }}</h2>
                            <small class="stat-change text-muted">Active suppliers</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('quotations.index') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-info" style="cursor: pointer;">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Active Quotations</p>
                            <h2 class="stat-value">{{ $activeQuotations }}</h2>
                            <small class="stat-change text-muted">Pending review</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-2">
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Purchase Orders by Status</h5>
                <i class="bi bi-bar-chart chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="poStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Purchase Requests by Status</h5>
                <i class="bi bi-pie-chart chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="prStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Trend Charts Row -->
<div class="row mb-2">
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Monthly Purchase Orders Trend</h5>
                <i class="bi bi-graph-up-arrow chart-icon"></i>
            </div>
            <div class="chart-card-filter p-3 border-bottom">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2" id="monthlyPOFilter">
                    <input type="hidden" name="date_from" value="{{ request('date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="po_date_from" 
                            value="{{ request('po_date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyPOFilter').submit()">
                    </div>
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="po_date_to" 
                            value="{{ request('po_date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyPOFilter').submit()">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chart-card-body">
                <canvas id="monthlyPOChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Top Suppliers by Orders</h5>
                <i class="bi bi-trophy chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="topSuppliersChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="activity-card">
            <div class="activity-card-header">
                <h5 class="activity-title">Recent Purchase Orders</h5>
                <a href="{{ route('purchase-orders.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentPOs as $po)
                    <a href="{{ route('purchase-orders.show', $po) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $po->po_number }}</h6>
                                <span class="badge badge-{{ $po->status === 'approved' ? 'success' : ($po->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($po->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-truck"></i> {{ $po->supplier->name ?? 'N/A' }} • 
                                <i class="bi bi-clock"></i> {{ $po->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No purchase orders yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="activity-card">
            <div class="activity-card-header">
                <h5 class="activity-title">Recent Purchase Requests</h5>
                <a href="{{ route('purchase-requests.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentPRs as $pr)
                    <a href="{{ route('purchase-requests.show', $pr) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $pr->pr_number }}</h6>
                                <span class="badge badge-{{ $pr->status === 'approved' ? 'success' : ($pr->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($pr->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-briefcase"></i> {{ $pr->project->name ?? 'N/A' }} • 
                                <i class="bi bi-clock"></i> {{ $pr->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No purchase requests yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="activity-card">
            <div class="activity-card-header">
                <h5 class="activity-title">Recent Quotations</h5>
                <a href="{{ route('quotations.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentQuotations as $quotation)
                    <a href="{{ route('quotations.show', $quotation) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $quotation->quotation_number }}</h6>
                                <span class="badge badge-{{ $quotation->status === 'accepted' ? 'success' : ($quotation->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($quotation->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-truck"></i> {{ $quotation->supplier->name ?? 'N/A' }} • 
                                <i class="bi bi-clock"></i> {{ $quotation->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No quotations yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@include('dashboards.partials.styles')
@include('dashboards.partials.scripts-purchasing')

@endsection

