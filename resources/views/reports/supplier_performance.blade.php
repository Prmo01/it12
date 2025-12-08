@extends('layouts.app')

@section('title', 'Supplier Performance Report')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-truck"></i> Supplier Performance Report</h1>
        <p class="text-muted mb-0">Analyze supplier performance metrics and delivery statistics</p>
    </div>
    <div class="d-flex gap-2">
        @if($data->isNotEmpty())
        <a href="{{ route('reports.supplier-performance', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.supplier-performance', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card report-card">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.supplier-performance') }}" class="mb-4 filter-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label-custom-small">
                        <i class="bi bi-calendar-event"></i> Date From
                    </label>
                    <input type="date" name="date_from" class="form-control-custom" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label-custom-small">
                        <i class="bi bi-calendar-check"></i> Date To
                    </label>
                    <input type="date" name="date_to" class="form-control-custom" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['date_from', 'date_to']))
            <div class="mt-2">
                <a href="{{ route('reports.supplier-performance') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
            @endif
        </form>
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Total Orders</th>
                        <th>Completed Orders</th>
                        <th>Total Amount</th>
                        <th>On-Time Deliveries</th>
                        <th>On-Time Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item['supplier']->name }}</div>
                                <small class="text-muted">{{ $item['supplier']->email ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $item['total_orders'] }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $item['completed_orders'] }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">₱{{ number_format($item['total_amount'], 2) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $item['on_time_deliveries'] }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar {{ $item['on_time_rate'] >= 80 ? 'bg-success' : ($item['on_time_rate'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ min($item['on_time_rate'], 100) }}%">
                                        </div>
                                    </div>
                                    <span class="fw-semibold">{{ number_format($item['on_time_rate'], 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-truck"></i>
                                    <p class="mt-3 mb-0">No supplier data found</p>
                                    <small class="text-muted">Try adjusting your filters</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($data->isNotEmpty())
        <div class="mt-3 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="fw-semibold text-muted">Total Suppliers</div>
                    <div class="h4 mb-0">{{ $data->count() }}</div>
                </div>
                <div class="col-md-3">
                    <div class="fw-semibold text-muted">Total Orders</div>
                    <div class="h4 mb-0">{{ $data->sum('total_orders') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="fw-semibold text-muted">Total Amount</div>
                    <div class="h4 mb-0">₱{{ number_format($data->sum('total_amount'), 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="fw-semibold text-muted">Avg On-Time Rate</div>
                    <div class="h4 mb-0">{{ $data->count() > 0 ? number_format($data->avg('on_time_rate'), 1) : 0 }}%</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .report-card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
    }
    
    .filter-form {
        padding: 1.5rem;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    
    .form-label-custom-small {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label-custom-small i {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        color: #111827;
        background: #ffffff;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        background: #fafbff;
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
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
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
    
    .badge-info {
        background: #3b82f6;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .progress {
        background-color: #e5e7eb;
        border-radius: 4px;
    }
    
    .progress-bar {
        height: 100%;
        border-radius: 4px;
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

