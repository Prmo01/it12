@extends('layouts.app')

@section('title', 'Inventory Manager Dashboard')

@php
    $user = auth()->user();
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-speedometer2"></i> Inventory Manager Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, {{ $user->name ?? 'User' }}! Manage inventory and warehouse operations.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-2">
    <div class="col-md-3 mb-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-primary" style="cursor: pointer;">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Total Items</p>
                            <h2 class="stat-value">{{ $totalItems }}</h2>
                            <small class="stat-change text-muted">Inventory items</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('inventory.index') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-danger" style="cursor: pointer;">
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
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('goods-receipts.index') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-warning" style="cursor: pointer;">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Pending Receipts</p>
                            <h2 class="stat-value">{{ $pendingReceipts }}</h2>
                            <small class="stat-change text-muted">Awaiting processing</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('goods-returns.index') }}" class="text-decoration-none" style="color: inherit;">
            <div class="stat-card stat-card-info" style="cursor: pointer;">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Pending Returns</p>
                            <h2 class="stat-value">{{ $pendingReturns }}</h2>
                            <small class="stat-change text-muted">Needs processing</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-arrow-return-left"></i>
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
                <h5 class="chart-title">Goods Receipts by Status</h5>
                <i class="bi bi-pie-chart chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="receiptStatusChart"></canvas>
            </div>
        </div>
    </div>
    
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
</div>

<!-- Trend Charts Row -->
<div class="row mb-2">
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Monthly Receipts Trend</h5>
                <i class="bi bi-graph-up-arrow chart-icon"></i>
            </div>
            <div class="chart-card-filter p-3 border-bottom">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2" id="monthlyReceiptsFilter">
                    <input type="hidden" name="date_from" value="{{ request('date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="receipts_date_from" 
                            value="{{ request('receipts_date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyReceiptsFilter').submit()">
                    </div>
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="receipts_date_to" 
                            value="{{ request('receipts_date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyReceiptsFilter').submit()">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chart-card-body">
                <canvas id="monthlyReceiptsChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Monthly Issuances Trend</h5>
                <i class="bi bi-graph-down-arrow chart-icon"></i>
            </div>
            <div class="chart-card-filter p-3 border-bottom">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2" id="monthlyIssuancesFilter">
                    <input type="hidden" name="date_from" value="{{ request('date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="issuances_date_from" 
                            value="{{ request('issuances_date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyIssuancesFilter').submit()">
                    </div>
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="issuances_date_to" 
                            value="{{ request('issuances_date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyIssuancesFilter').submit()">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chart-card-body">
                <canvas id="monthlyIssuancesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="activity-card">
            <div class="activity-card-header">
                <h5 class="activity-title">Recent Goods Receipts</h5>
                <a href="{{ route('goods-receipts.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentReceipts as $receipt)
                    <a href="{{ route('goods-receipts.show', $receipt) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $receipt->gr_number }}</h6>
                                <span class="badge badge-{{ $receipt->status === 'approved' ? 'success' : ($receipt->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($receipt->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-truck"></i> {{ $receipt->supplier->name ?? 'N/A' }} • 
                                <i class="bi bi-clock"></i> {{ $receipt->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No goods receipts yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="activity-card">
            <div class="activity-card-header">
                <h5 class="activity-title">Recent Goods Returns</h5>
                <a href="{{ route('goods-returns.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentReturns as $return)
                    <a href="{{ route('goods-returns.show', $return) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $return->return_number }}</h6>
                                <span class="badge badge-{{ $return->status === 'approved' ? 'success' : ($return->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($return->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-arrow-return-left"></i> {{ $return->supplier->name ?? 'N/A' }} • 
                                <i class="bi bi-clock"></i> {{ $return->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No goods returns yet</p>
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
                @forelse($recentIssuances as $issuance)
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
@include('dashboards.partials.scripts-inventory-manager')

@endsection

