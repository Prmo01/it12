@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $user = auth()->user();
    $canAccessProjects = $user && $user->canAccessModule('projects');
    $canAccessPurchaseOrders = $user && $user->canAccessModule('purchase_orders');
    $canAccessInventory = $user && $user->canAccessModule('inventory');
    $canAccessMaterialIssuance = $user && $user->canAccessModule('material_issuance');
    $canAccessReports = $user && $user->canAccessModule('reports');
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-speedometer2"></i> Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, {{ $user->name ?? 'User' }}! Here's what's happening with your business today.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-2">
    @if($canAccessProjects)
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
    @endif
    
    @if($canAccessPurchaseOrders)
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
    @endif
    
    @if($canAccessInventory)
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
    @endif
</div>

<!-- Charts Row -->
@if($canAccessProjects || $canAccessPurchaseOrders)
<div class="row mb-2">
    @if($canAccessProjects)
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
    @endif
    
    @if($canAccessPurchaseOrders)
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
    @endif
</div>
@endif

<!-- Trend Charts Row -->
@if($canAccessProjects || $canAccessPurchaseOrders)
<div class="row mb-2">
    @if($canAccessPurchaseOrders)
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Monthly Purchase Orders Trend</h5>
                <i class="bi bi-graph-up-arrow chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="monthlyPOChart"></canvas>
            </div>
        </div>
    </div>
    @endif
    
    @if($canAccessProjects)
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Monthly Projects Trend</h5>
                <i class="bi bi-graph-up chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="monthlyProjectsChart"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Additional Charts Row -->
@if($canAccessInventory || ($canAccessPurchaseOrders && $canAccessReports))
<div class="row mb-2">
    @if($canAccessInventory)
    <div class="col-md-6 mb-3">
        <div class="chart-card">
            <div class="chart-card-header">
                <h5 class="chart-title">Inventory Movements (Last 30 Days)</h5>
                <i class="bi bi-arrow-left-right chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="inventoryMovementChart"></canvas>
            </div>
        </div>
    </div>
    @endif
    
    @if($canAccessPurchaseOrders && $canAccessReports)
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
    @endif
</div>
@endif

<!-- Recent Activities -->
<div class="row">
    @if($canAccessProjects)
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
    @endif
    
    @if($canAccessPurchaseOrders)
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
    @endif
    
    @if($canAccessMaterialIssuance)
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
    @endif
</div>

@push('styles')
<style>
    /* Stat Cards */
    .stat-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        position: relative;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
    }
    
    .stat-card-primary {
        --card-color: #2563eb;
        --card-color-light: #3b82f6;
    }
    
    .stat-card-success {
        --card-color: #10b981;
        --card-color-light: #34d399;
    }
    
    .stat-card-warning {
        --card-color: #f59e0b;
        --card-color-light: #fbbf24;
    }
    
    .stat-card-danger {
        --card-color: #ef4444;
        --card-color-light: #f87171;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    
    .stat-card-body {
        padding: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        margin: 0 0 0.5rem 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .stat-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 0.25rem 0;
        line-height: 1;
    }
    
    .stat-change {
        font-size: 0.75rem;
        display: block;
    }
    
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--card-color), var(--card-color-light));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.75rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Chart Cards */
    .chart-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .chart-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .chart-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
    }
    
    .chart-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }
    
    .chart-icon {
        color: #6b7280;
        font-size: 1.25rem;
    }
    
    .chart-card-body {
        padding: 1.5rem;
        height: 320px;
        position: relative;
    }
    
    .chart-card-header {
        background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    }
    
    .chart-card:hover .chart-card-header {
        background: linear-gradient(135deg, #f3f4f6 0%, #f9fafb 100%);
    }
    
    /* Activity Cards */
    .activity-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .activity-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .activity-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
    }
    
    .activity-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }
    
    .activity-link {
        font-size: 0.875rem;
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        transition: color 0.2s ease;
    }
    
    .activity-link:hover {
        color: #1d4ed8;
    }
    
    .activity-card-body {
        padding: 0.75rem;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
    }
    
    .activity-item:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }
    
    .activity-item:last-child {
        margin-bottom: 0;
    }
    
    .activity-item-content {
        flex: 1;
    }
    
    .activity-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .activity-item-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }
    
    .activity-item-meta {
        font-size: 0.8125rem;
        color: #6b7280;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .activity-arrow {
        color: #9ca3af;
        font-size: 1.125rem;
        transition: transform 0.2s ease;
    }
    
    .activity-item:hover .activity-arrow {
        transform: translateX(4px);
        color: #2563eb;
    }
    
    .activity-empty {
        padding: 3rem 1.5rem;
        text-align: center;
        color: #9ca3af;
    }
    
    .activity-empty i {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        display: block;
    }
    
    .activity-empty p {
        margin: 0;
        font-size: 0.875rem;
    }
    
    /* Badges */
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-secondary {
        background: #6b7280;
        color: #ffffff;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-info {
        background: #3b82f6;
        color: #ffffff;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-primary {
        background: #2563eb;
        color: #ffffff;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    // Helper function to create gradient
    function createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }

    // Enhanced Project Status Chart with gradients
    @if($canAccessProjects)
    const projectStatusChartEl = document.getElementById('projectStatusChart');
    if (projectStatusChartEl) {
        const projectCtx = projectStatusChartEl.getContext('2d');
    const projectGradients = [
        createGradient(projectCtx, 'rgba(37, 99, 235, 0.9)', 'rgba(37, 99, 235, 0.6)'),
        createGradient(projectCtx, 'rgba(16, 185, 129, 0.9)', 'rgba(16, 185, 129, 0.6)'),
        createGradient(projectCtx, 'rgba(245, 158, 11, 0.9)', 'rgba(245, 158, 11, 0.6)'),
        createGradient(projectCtx, 'rgba(239, 68, 68, 0.9)', 'rgba(239, 68, 68, 0.6)'),
        createGradient(projectCtx, 'rgba(107, 114, 128, 0.9)', 'rgba(107, 114, 128, 0.6)')
    ];
    
    new Chart(projectCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($projectStatusData->toArray())) !!},
            datasets: [{
                data: {!! json_encode(array_values($projectStatusData->toArray())) !!},
                backgroundColor: [
                    '#2563eb',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#6b7280',
                    '#8b5cf6',
                    '#ec4899'
                ],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 12,
                hoverBorderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1500,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 10,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    }
    @endif
    
    // Enhanced PO Status Chart with gradient bars
    @if($canAccessPurchaseOrders)
    const poStatusChartEl = document.getElementById('poStatusChart');
    if (poStatusChartEl) {
        const poCtx = poStatusChartEl.getContext('2d');
    const poGradient = createGradient(poCtx, 'rgba(37, 99, 235, 0.9)', 'rgba(59, 130, 246, 0.6)');
    const poLabels = {!! json_encode(array_keys($poStatusData->toArray())) !!};
    const poData = {!! json_encode(array_values($poStatusData->toArray())) !!};
    const poBarColors = poLabels.map((label) => {
        const normalized = (label || '').toString().toLowerCase();
        return normalized.includes('draft') ? '#9ca3af' : poGradient;
    });
    const poBorderColors = poLabels.map((label) => {
        const normalized = (label || '').toString().toLowerCase();
        return normalized.includes('draft') ? '#6b7280' : '#2563eb';
    });
    
    new Chart(poCtx, {
        type: 'bar',
        data: {
            labels: poLabels,
            datasets: [{
                label: 'Purchase Orders',
                data: poData,
                backgroundColor: poBarColors,
                borderColor: poBorderColors,
                borderWidth: 2,
                borderRadius: 10,
                borderSkipped: false,
                barThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false,
                        lineWidth: 1
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280',
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
    }
    @endif

    // Monthly Purchase Orders Trend Line Chart
    @if($canAccessPurchaseOrders)
    const monthlyPOChartEl = document.getElementById('monthlyPOChart');
    if (monthlyPOChartEl) {
        const monthlyPOCtx = monthlyPOChartEl.getContext('2d');
    const monthlyPOGradient = createGradient(monthlyPOCtx, 'rgba(16, 185, 129, 0.3)', 'rgba(16, 185, 129, 0.05)');
    
    const monthlyPOLabels = {!! json_encode(array_keys($monthlyPOs->toArray())) !!};
    const monthlyPOData = {!! json_encode(array_values($monthlyPOs->toArray())) !!};
    
    new Chart(monthlyPOCtx, {
        type: 'line',
        data: {
            labels: monthlyPOLabels.map(month => {
                const date = new Date(month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Purchase Orders',
                data: monthlyPOData,
                borderColor: '#10b981',
                backgroundColor: monthlyPOGradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: '#059669',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 10,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280',
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
    }
    @endif

    // Monthly Projects Trend Line Chart
    @if($canAccessProjects)
    const monthlyProjectsChartEl = document.getElementById('monthlyProjectsChart');
    if (monthlyProjectsChartEl) {
        const monthlyProjectsCtx = monthlyProjectsChartEl.getContext('2d');
    const monthlyProjectsGradient = createGradient(monthlyProjectsCtx, 'rgba(37, 99, 235, 0.3)', 'rgba(37, 99, 235, 0.05)');
    
    const monthlyProjectsLabels = {!! json_encode(array_keys($monthlyProjects->toArray())) !!};
    const monthlyProjectsData = {!! json_encode(array_values($monthlyProjects->toArray())) !!};
    
    new Chart(monthlyProjectsCtx, {
        type: 'line',
        data: {
            labels: monthlyProjectsLabels.map(month => {
                const date = new Date(month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Projects',
                data: monthlyProjectsData,
                borderColor: '#2563eb',
                backgroundColor: monthlyProjectsGradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: '#1d4ed8',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 10,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280',
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
    }
    @endif

    // Inventory Movements Chart
    @if($canAccessInventory)
    const inventoryMovementChartEl = document.getElementById('inventoryMovementChart');
    if (inventoryMovementChartEl) {
        const inventoryCtx = inventoryMovementChartEl.getContext('2d');
    const inventoryData = {!! json_encode($inventoryMovements) !!};
    
    const inventoryDates = Object.keys(inventoryData);
    const inData = inventoryDates.map(date => {
        const dayData = inventoryData[date];
        const inMovement = dayData.find(m => m.movement_type === 'in' || m.movement_type.includes('in'));
        return inMovement ? parseFloat(inMovement.total) : 0;
    });
    const outData = inventoryDates.map(date => {
        const dayData = inventoryData[date];
        const outMovement = dayData.find(m => m.movement_type === 'out' || m.movement_type.includes('out'));
        return outMovement ? parseFloat(outMovement.total) : 0;
    });
    
    new Chart(inventoryCtx, {
        type: 'bar',
        data: {
            labels: inventoryDates.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Stock In',
                data: inData,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 6
            }, {
                label: 'Stock Out',
                data: outData,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: '#ef4444',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 10,
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
    }
    @endif

    // Top Suppliers Chart
    @if($canAccessPurchaseOrders && $canAccessReports)
    const topSuppliersChartEl = document.getElementById('topSuppliersChart');
    if (topSuppliersChartEl) {
        const suppliersCtx = topSuppliersChartEl.getContext('2d');
    const suppliersData = {!! json_encode($topSuppliers) !!};
    
    const supplierNames = suppliersData.map(s => s.supplier ? s.supplier.name : 'Unknown');
    const supplierOrders = suppliersData.map(s => s.order_count);
    
    const suppliersGradient = createGradient(suppliersCtx, 'rgba(245, 158, 11, 0.9)', 'rgba(245, 158, 11, 0.6)');
    
    new Chart(suppliersCtx, {
        type: 'bar',
        data: {
            labels: supplierNames,
            datasets: [{
                label: 'Orders',
                data: supplierOrders,
                backgroundColor: suppliersGradient,
                borderColor: '#f59e0b',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 10,
                    callbacks: {
                        afterLabel: function(context) {
                            const supplier = suppliersData[context.dataIndex];
                            return 'Total: ₱' + parseFloat(supplier.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280',
                        stepSize: 1
                    }
                },
                y: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
    }
    @endif
</script>
@endpush
@endsection
