<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP System') - Construction Fabrication ERP</title>
    
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
            font-size: 18px; /* Base font size increased for better readability */
        }
        
        body {
            padding-left: 0;
            font-size: 1rem; /* 18px - increased body text size */
            line-height: 1.6;
        }
        
        /* Normalize common text elements */
        p, span, div, label, input, select, textarea, button, a {
            font-size: inherit;
        }
        
        /* Normalize headings to reasonable sizes */
        h1 {
            font-size: 1.75rem; /* 31.5px - increased */
        }
        
        h2 {
            font-size: 1.5rem; /* 27px - increased */
        }
        
        h3 {
            font-size: 1.25rem; /* 22.5px - increased */
        }
        
        h4 {
            font-size: 1.125rem; /* 20.25px - increased */
        }
        
        h5 {
            font-size: 1rem; /* 18px - increased */
        }
        
        h6 {
            font-size: 0.9375rem; /* 16.875px - increased */
        }
        
        /* Normalize table text */
        table {
            font-size: 1rem; /* 18px - increased */
        }
        
        /* Normalize form elements */
        .form-control, .form-select, .form-control-custom {
            font-size: 1rem; /* 18px - increased */
        }
        
        .form-label, .form-label-custom {
            font-size: 1rem; /* 18px - increased */
        }
        
        /* Normalize buttons */
        .btn {
            font-size: 1rem; /* 18px - increased */
        }
        
        .btn-sm {
            font-size: 0.9375rem; /* 16.875px - increased */
        }
        
        .btn-lg {
            font-size: 1.125rem; /* 20.25px - increased */
        }
        
        /* Normalize badges */
        .badge {
            font-size: 0.875rem; /* 15.75px - increased */
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
            padding: 0.375rem 0.25rem;
        }
        
        @media (min-width: 768px) {
            .main-content {
                margin-left: 320px;
                width: calc(100% - 320px);
                padding: 0.5rem 0.5rem;
            }
        }
        
        @media (min-width: 992px) {
            .main-content {
                margin-left: 320px;
                width: calc(100% - 320px);
                padding: 0.625rem 0.75rem;
            }
        }
        
        .main-content .container-fluid {
            padding-left: 0;
            padding-right: 0;
            max-width: 100%;
        }
        
        /* Page Header Styling */
        .page-header {
            padding-top: 0.375rem;
            padding-bottom: 0.375rem;
            margin-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .page-header h1 {
            font-size: 1.75rem; /* 31.5px - increased */
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .page-header h1.h2 {
            font-size: 1.5rem; /* 27px - increased */
        }
        
        .page-header p {
            font-size: 1rem; /* 18px - increased */
            margin-bottom: 0;
        }
        
        /* Card spacing - consistent across all pages */
        .card {
            margin-bottom: 0.875rem;
        }
        
        .card:last-child {
            margin-bottom: 0;
        }
        
        /* Row spacing */
        .row {
            margin-bottom: 0.875rem;
        }
        
        .row:last-child {
            margin-bottom: 0;
        }
        
        /* Form card spacing */
        .form-card {
            margin-bottom: 0.875rem;
        }
        
        /* Info card spacing */
        .info-card {
            margin-bottom: 0.875rem;
        }
        
        /* Section spacing */
        .content-section {
            margin-bottom: 0.875rem;
        }
        
        /* Reduce excessive bottom padding */
        .main-content .container-fluid {
            padding-bottom: 0.25rem;
            padding-top: 0;
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
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

