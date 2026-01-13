@extends('layouts.app')

@section('title', 'Project Manager Dashboard')

@php
    $user = auth()->user();
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-speedometer2"></i> Project Manager Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, {{ $user->name ?? 'User' }}! Manage your projects and track progress.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="{{ route('projects.index') }}" class="stat-card-link">
            <div class="stat-card stat-card-primary h-100">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">My Projects</p>
                            <h2 class="stat-value">{{ $myProjects }}</h2>
                            <small class="stat-change text-muted">Active projects</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-folder"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3">
        <a href="{{ route('projects.index', ['status' => 'active']) }}" class="stat-card-link">
            <div class="stat-card stat-card-success h-100">
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
        </a>
    </div>
    
    <div class="col-md-3">
        <a href="{{ route('change-orders.index') }}" class="stat-card-link">
            <div class="stat-card stat-card-warning h-100">
                <div class="stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-content">
                            <p class="stat-label">Pending Change Orders</p>
                            <h2 class="stat-value">{{ $pendingChangeOrders }}</h2>
                            <small class="stat-change text-muted">Awaiting approval</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-3">
        <a href="{{ route('purchase-requests.index') }}" class="stat-card-link">
            <div class="stat-card stat-card-info h-100">
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
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <h5 class="chart-title">Projects by Status</h5>
                <i class="bi bi-pie-chart chart-icon"></i>
            </div>
            <div class="chart-card-body">
                <canvas id="projectStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="chart-card h-100">
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

<!-- Recent Activities -->
<div class="row g-3">
    <div class="col-md-4">
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
                                <span class="status-text status-text-{{ $project->status === 'active' ? 'success' : ($project->status === 'planning' ? 'primary' : 'secondary') }}">{{ ucfirst($project->status) }}</span>
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
    
    <div class="col-md-4">
        <div class="activity-card h-100">
            <div class="activity-card-header">
                <h5 class="activity-title">Recent Change Orders</h5>
                <a href="{{ route('change-orders.index') }}" class="activity-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-card-body">
                @forelse($recentChangeOrders as $co)
                    <a href="{{ route('change-orders.show', $co) }}" class="activity-item">
                        <div class="activity-item-content">
                            <div class="activity-item-header">
                                <h6 class="activity-item-title">{{ $co->co_number }}</h6>
                                <span class="status-text status-text-{{ $co->status === 'approved' ? 'success' : ($co->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($co->status) }}</span>
                            </div>
                            <p class="activity-item-meta">
                                <i class="bi bi-briefcase"></i> {{ $co->project->name ?? 'N/A' }} • 
                                <i class="bi bi-clock"></i> {{ $co->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <i class="bi bi-chevron-right activity-arrow"></i>
                    </a>
                @empty
                    <div class="activity-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No change orders yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="activity-card h-100">
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
                                <span class="status-text status-text-{{ $pr->status === 'approved' ? 'success' : ($pr->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($pr->status) }}</span>
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
</div>

@include('dashboards.partials.styles')
@include('dashboards.partials.scripts-project-manager')

@endsection

