@extends('layouts.app')

@section('title', 'Admin Dashboard')

@php
    $user = auth()->user();
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-speedometer2"></i> Admin Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, {{ $user->name ?? 'User' }}! Complete system overview and management.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-2">
    <div class="col-md-3 mb-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-content">
                        <p class="stat-label">Total Projects</p>
                        <h2 class="stat-value">{{ $totalProjects }}</h2>
                        <small class="stat-change text-muted">Excluding completed</small>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-folder"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-content">
                        <p class="stat-label">Active Projects</p>
                        <h2 class="stat-value">{{ $activeProjects }}</h2>
                        <small class="stat-change text-muted">In progress</small>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
        <div class="stat-card stat-card-danger">
            <div class="stat-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-content">
                        <p class="stat-label">Low Stock Items</p>
                        <h2 class="stat-value">{{ $lowStockItems }}</h2>
                        <small class="stat-change text-muted">Needs attention</small>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-2">
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Projects by Status</h5>
                <i class="bi bi-pie-chart chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="projectStatusChart"></canvas>
            </div>
        </div>
    </div>
    
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
                <h5 class="chart-title">Monthly Projects Trend</h5>
                <i class="bi bi-graph-up chart-icon"></i>
            </div>
            <div class="chart-card-filter p-3 border-bottom">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2" id="monthlyProjectsFilter">
                    <input type="hidden" name="date_from" value="{{ request('date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="projects_date_from" 
                            value="{{ request('projects_date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyProjectsFilter').submit()">
                    </div>
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="projects_date_to" 
                            value="{{ request('projects_date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyProjectsFilter').submit()">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chart-card-body">
                <canvas id="monthlyProjectsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Additional Charts Row -->
<div class="row mb-2">
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Inventory Movements</h5>
                <i class="bi bi-arrow-left-right chart-icon"></i>
            </div>
            <div class="chart-card-filter p-3 border-bottom">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2" id="inventoryMovementFilter">
                    <input type="hidden" name="date_from" value="{{ request('date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="inventory_date_from" 
                            value="{{ request('inventory_date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subDays(30)->format('Y-m-d')) }}"
                            onchange="document.getElementById('inventoryMovementFilter').submit()">
                    </div>
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="inventory_date_to" 
                            value="{{ request('inventory_date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                            onchange="document.getElementById('inventoryMovementFilter').submit()">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chart-card-body">
                <canvas id="inventoryMovementChart"></canvas>
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
                <h5 class="activity-title">Recent Projects</h5>
                <a href="{{ route('projects.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentProjects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $project->name }}</h6>
                                <span class="badge badge-{{ $project->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($project->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-clock"></i> {{ $project->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No projects yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
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
                                <span class="badge badge-info">{{ $po->supplier->name ?? 'N/A' }}</span>
                            </div>
                            <p class="activity-item-meta">
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
                <h5 class="activity-title">Recent Material Issuances</h5>
                <a href="{{ route('material-issuance.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentMaterialIssuances as $issuance)
                    <a href="{{ route('material-issuance.show', $issuance) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $issuance->issuance_number }}</h6>
                                <span class="badge badge-{{ $issuance->status === 'issued' ? 'success' : ($issuance->status === 'approved' ? 'primary' : 'warning') }}">{{ ucfirst($issuance->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-briefcase"></i> {{ $issuance->project->name ?? 'N/A' }} • 
                                <i class="bi bi-clock"></i> {{ $issuance->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No material issuances yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@include('dashboards.partials.styles')
@include('dashboards.partials.scripts-admin')

@endsection

