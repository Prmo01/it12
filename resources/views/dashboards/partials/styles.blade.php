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
        background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    }
    
    .chart-card:hover .chart-card-header {
        background: linear-gradient(135deg, #f3f4f6 0%, #f9fafb 100%);
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
    
    .badge-warning {
        background: #f59e0b;
        color: #ffffff;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .stat-card-info {
        --card-color: #3b82f6;
        --card-color-light: #60a5fa;
    }
</style>
@endpush

