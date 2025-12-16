@extends('layouts.app')

@section('title', 'Warehouse Manager Dashboard')

@php
    $user = auth()->user();
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-speedometer2"></i> Warehouse Manager Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, {{ $user->name ?? 'User' }}! Manage warehouse operations and quality inspections.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-2">
    <div class="col-md-3 mb-3">
        <a href="{{ route('goods-receipts.index') }}" class="stat-card-link">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Pending Inspections</p>
                            <h2 class="stat-value">{{ $pendingInspections }}</h2>
                            <small class="stat-change text-muted">Awaiting quality check</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('goods-returns.index') }}" class="stat-card-link">
            <div class="stat-card stat-card-primary">
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
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('goods-receipts.index', ['status' => 'approved']) }}" class="stat-card-link">
            <div class="stat-card stat-card-success">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Approved Today</p>
                            <h2 class="stat-value">{{ $approvedToday }}</h2>
                            <small class="stat-change text-muted">Quality approved</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="{{ route('goods-receipts.index', ['status' => 'rejected']) }}" class="stat-card-link">
            <div class="stat-card stat-card-danger">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Rejected Today</p>
                            <h2 class="stat-value">{{ $rejectedToday }}</h2>
                            <small class="stat-change text-muted">Failed quality check</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-x-circle"></i>
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
                <h5 class="chart-title">Monthly Approvals Trend</h5>
                <i class="bi bi-graph-up-arrow chart-icon"></i>
            </div>
            <div class="chart-card-filter p-3 border-bottom">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2" id="monthlyApprovalsFilter">
                    <input type="hidden" name="date_from" value="{{ request('date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="approvals_date_from" 
                            value="{{ request('approvals_date_from', isset($dateFrom) ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : now()->subMonths(6)->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyApprovalsFilter').submit()">
                    </div>
                    <div class="col-5">
                        <input type="date" class="form-control form-control-sm" name="approvals_date_to" 
                            value="{{ request('approvals_date_to', isset($dateTo) ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                            onchange="document.getElementById('monthlyApprovalsFilter').submit()">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chart-card-body">
                <canvas id="monthlyApprovalsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Movements Chart -->
<div class="row mb-2">
    <div class="col-md-12 mb-3">
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

<!-- Recent Activities -->
<div class="row">
    <div class="col-md-6 mb-3">
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
                                <span class="status-text status-text-{{ $receipt->status === 'approved' ? 'success' : ($receipt->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($receipt->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-truck"></i> {{ $receipt->purchaseOrder->supplier->name ?? 'N/A' }} • 
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
    
    <div class="col-md-6 mb-3">
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
                                <span class="status-text status-text-{{ $return->status === 'approved' ? 'success' : ($return->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($return->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-arrow-return-left"></i> {{ $return->goodsReceipt->purchaseOrder->supplier->name ?? 'N/A' }} • 
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
</div>

@include('dashboards.partials.styles')
@include('dashboards.partials.scripts-warehouse-manager')

@endsection

