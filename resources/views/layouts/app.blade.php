<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP System') - Construction Fabrication ERP</title>
    
    <!-- Favicon - Using Davao logo from login page -->
    <link rel="icon" type="image/png" href="{{ asset('images/davao.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/davao.png') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    @stack('styles')
    <style>
        /* Base font size normalization - applies to all views */
        html {
            font-size: 20px; /* Base font size increased for better readability */
        }
        
        body {
            padding-left: 0;
            font-size: 1rem; /* 20px - increased body text size */
            line-height: 1.7; /* Improved line height for better readability */
            color: #1e293b;
        }
        
        /* Normalize common text elements */
        p, span, div, label, input, select, textarea, button, a {
            font-size: inherit;
        }
        
        /* Enhanced headings with better hierarchy */
        h1 {
            font-size: 2rem; /* 40px - increased */
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.02em;
        }
        
        h2 {
            font-size: 1.75rem; /* 35px - increased */
            font-weight: 700;
            line-height: 1.35;
            letter-spacing: -0.01em;
        }
        
        h3 {
            font-size: 1.5rem; /* 30px - increased */
            font-weight: 600;
            line-height: 1.4;
        }
        
        h4 {
            font-size: 1.25rem; /* 25px - increased */
            font-weight: 600;
            line-height: 1.45;
        }
        
        h5 {
            font-size: 1.125rem; /* 22.5px - increased */
            font-weight: 600;
            line-height: 1.5;
        }
        
        h6 {
            font-size: 1rem; /* 20px - increased */
            font-weight: 600;
            line-height: 1.5;
        }
        
        /* Normalize table text */
        table {
            font-size: 1rem; /* 20px - increased */
        }
        
        /* Enhanced form elements with better spacing */
        .form-control, .form-select, .form-control-custom {
            font-size: 1rem; /* 20px - increased */
            padding: 0.75rem 1rem; /* Increased padding */
            line-height: 1.6;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus, .form-control-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        
        .form-label, .form-label-custom {
            font-size: 1rem; /* 20px - increased */
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #334155;
        }
        
        /* Enhanced buttons with better spacing */
        .btn {
            font-size: 1rem; /* 20px - increased */
            padding: 0.75rem 1.5rem; /* Increased padding */
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
            line-height: 1.5;
        }
        
        .btn-sm {
            font-size: 0.9375rem; /* 18.75px - increased */
            padding: 0.5rem 1rem;
        }
        
        .btn-lg {
            font-size: 1.125rem; /* 22.5px - increased */
            padding: 1rem 2rem;
        }
        
        /* Enhanced badges */
        .badge {
            font-size: 0.875rem; /* 17.5px - increased */
            padding: 0.5rem 0.75rem;
            font-weight: 600;
        }
        
        /* Status Text (replacing badges for better UX) */
        .status-text {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0;
            border-radius: 0;
            display: inline-block;
        }
        
        .status-text-success {
            color: #10b981;
        }
        
        .status-text-secondary {
            color: #6b7280;
        }
        
        .status-text-info {
            color: #3b82f6;
        }
        
        .status-text-primary {
            color: #2563eb;
        }
        
        .status-text-warning {
            color: #f59e0b;
        }
        
        .status-text-danger {
            color: #ef4444;
        }
        
        /* Project Code and Supplier Text (emphasized but not in boxes) */
        .project-code-text {
            color: #3b82f6;
            font-weight: 600;
            font-family: monospace;
        }
        
        .supplier-text {
            color: #2563eb;
            font-weight: 600;
        }
        
        /* Normalize small text */
        small, .small {
            font-size: 0.9375rem; /* 16.875px - increased */
        }
        
        @media (min-width: 768px) {
            body {
                padding-left: 320px;
            }
            
            .sidebar {
                left: 0 !important;
            }
            
            .sidebar.collapse {
                display: block !important;
            }
            
            .sidebar-backdrop {
                display: none !important;
            }
        }
        
        @media (min-width: 992px) {
            body {
                padding-left: 30px;
            }
        }
        
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 1rem 1rem; /* Increased padding */
        }
        
        @media (min-width: 768px) {
            .main-content {
                margin-left: 320px;
                width: calc(100% - 320px);
                padding: 1.5rem 1.5rem; /* Increased padding */
            }
        }
        
        @media (min-width: 992px) {
            .main-content {
                margin-left: 320px;
                width: calc(100% - 320px);
                padding: 2rem 2rem; /* Increased padding */
            }
        }
        
        .main-content .container-fluid {
            padding-left: 0;
            padding-right: 0;
            max-width: 100%;
        }
        
        /* Enhanced Page Header Styling */
        .page-header {
            padding-top: 1.5rem; /* Increased padding */
            padding-bottom: 1.25rem; /* Increased padding */
            margin-bottom: 2rem; /* Increased margin */
            border-bottom: 2px solid #e5e7eb; /* Thicker border */
        }
        
        .page-header h1 {
            font-size: 2rem; /* 40px - increased */
            font-weight: 700;
            margin-bottom: 0.75rem; /* Increased margin */
            color: #0f172a;
        }
        
        .page-header h1.h2 {
            font-size: 1.75rem; /* 35px - increased */
        }
        
        .page-header p {
            font-size: 1.125rem; /* 22.5px - increased */
            margin-bottom: 0;
            color: #64748b;
            line-height: 1.6;
        }
        
        /* Enhanced Card spacing - consistent across all pages */
        .card {
            margin-bottom: 1.5rem; /* Increased spacing */
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .card-body {
            padding: 1.5rem; /* Increased padding */
        }
        
        .card-header {
            padding: 1.25rem 1.5rem; /* Increased padding */
            font-size: 1.125rem; /* Increased font size */
            font-weight: 600;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card:last-child {
            margin-bottom: 0;
        }
        
        /* Enhanced Row spacing */
        .row {
            margin-bottom: 1.5rem; /* Increased spacing */
        }
        
        .row:last-child {
            margin-bottom: 0;
        }
        
        /* Form card spacing */
        .form-card {
            margin-bottom: 1.5rem; /* Increased spacing */
        }
        
        /* Info card spacing */
        .info-card {
            margin-bottom: 1.5rem; /* Increased spacing */
        }
        
        /* Section spacing */
        .content-section {
            margin-bottom: 2rem; /* Increased spacing */
        }
        
        /* Enhanced main content padding */
        .main-content .container-fluid {
            padding-bottom: 2rem; /* Increased padding */
            padding-top: 0;
        }
        
        /* Enhanced table styling */
        .table {
            font-size: 1rem; /* 20px */
        }
        
        .table thead th {
            font-size: 0.9375rem; /* 18.75px */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            padding: 1rem 0.75rem; /* Increased padding */
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 1rem 0.75rem; /* Increased padding */
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Ensure consistent table cell structure - no overflowing text */
        .table tbody td {
            max-width: 400px;
        }
        
        .table tbody td:first-child,
        .table tbody td:last-child {
            max-width: none;
        }
        
        /* Status text alignment */
        .status-text {
            display: inline-block;
            white-space: nowrap;
        }
        
        /* Enhanced alert styling */
        .alert {
            font-size: 1rem; /* 20px */
            padding: 1rem 1.25rem; /* Increased padding */
            border-radius: 8px;
            margin-bottom: 1.5rem; /* Increased margin */
        }
        
        /* Enhanced stat cards */
        .stat-card {
            padding: 1.5rem; /* Increased padding */
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .stat-label {
            font-size: 0.9375rem; /* 18.75px */
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2rem; /* 40px */
            font-weight: 700;
            color: #0f172a;
            margin: 0.5rem 0;
        }
        
        /* Enhanced input groups */
        .input-group-custom {
            position: relative;
        }
        
        .input-group-custom .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.125rem;
            z-index: 10;
        }
        
        .input-group-custom .form-control-custom {
            padding-left: 2.75rem;
        }
        
        /* Enhanced spacing for form groups */
        .mb-3, .mb-4 {
            margin-bottom: 1.5rem !important; /* Increased spacing */
        }
        
        .mt-3, .mt-4 {
            margin-top: 1.5rem !important; /* Increased spacing */
        }
        
        /* Enhanced gap utilities */
        .gap-2 {
            gap: 0.75rem !important; /* Increased gap */
        }
        
        .gap-3 {
            gap: 1rem !important; /* Increased gap */
        }
        
        /* Mobile sidebar overlay */
        @media (max-width: 767.98px) {
            body {
                padding-left: 0 !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
            
            .sidebar {
                position: fixed;
                left: -320px;
                transition: left 0.3s ease;
                width: 320px !important;
            }
            
            .sidebar.show,
            .sidebar.collapse.show {
                left: 0;
            }
            
            .sidebar.collapse:not(.show) {
                left: -280px;
            }
            
            /* Backdrop for mobile */
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                display: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .sidebar.show ~ .sidebar-backdrop,
            .sidebar.collapse.show ~ .sidebar-backdrop {
                display: block;
                opacity: 1;
            }
            
            .sidebar-toggle {
                position: sticky;
                top: 1rem;
                z-index: 10;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    @include('layouts.sidebar')
    
    <!-- Mobile backdrop -->
    <div class="sidebar-backdrop" data-bs-toggle="collapse" data-bs-target="#sidebar"></div>
    
    <!-- Main Content -->
    <main class="main-content">
                <!-- Mobile Sidebar Toggle -->
                <button class="btn btn-primary d-md-none mb-3 sidebar-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-4 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>{{ session('success') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-4 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
        <div class="container-fluid px-0">
            @yield('content')
        </div>
    </main>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle sidebar backdrop click on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            
            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        const bsCollapse = new bootstrap.Collapse(sidebar, {
                            toggle: false
                        });
                        bsCollapse.hide();
                    }
                });
            }
            
            // Auto-hide sidebar on mobile when clicking a link
            if (window.innerWidth < 768) {
                const sidebarLinks = sidebar.querySelectorAll('.nav-link:not(.nav-link-group)');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        const bsCollapse = new bootstrap.Collapse(sidebar, {
                            toggle: false
                        });
                        bsCollapse.hide();
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

