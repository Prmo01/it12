@php
    $user = auth()->user();
    
    // Navigation visibility checks (following Golden Rule: only show if user can perform actions)
    $showDashboard = !$user || $user->isAdmin() || $user->shouldShowModuleInNavigation('dashboard');
    $showProjects = $user && $user->shouldShowModuleInNavigation('projects');
    $showChangeOrders = $user && $user->shouldShowModuleInNavigation('change_orders');
    $showPurchaseRequests = $user && $user->shouldShowModuleInNavigation('purchase_requests');
    $showQuotations = $user && $user->shouldShowModuleInNavigation('quotations');
    $showPurchaseOrders = $user && $user->shouldShowModuleInNavigation('purchase_orders');
    $showGoodsReceipts = $user && $user->shouldShowModuleInNavigation('goods_receipts');
    $showGoodsReturns = $user && $user->shouldShowModuleInNavigation('goods_returns');
    $showInventory = $user && $user->shouldShowModuleInNavigation('inventory');
    $showMaterialIssuance = $user && $user->shouldShowModuleInNavigation('material_issuance');
    $showSuppliers = $user && $user->shouldShowModuleInNavigation('suppliers');
    $showReports = $user && $user->shouldShowModuleInNavigation('reports');
    $showUsers = $user && $user->shouldShowModuleInNavigation('users');
    $showAuditLogs = $user && $user->isAdmin();
    
    // Check if purchasing menu should be shown (only if at least one sub-item is visible)
    $showPurchasingMenu = $showPurchaseRequests || $showQuotations || $showPurchaseOrders;
    
    // Check if inventory menu should be shown (only if at least one sub-item is visible)
    $showInventoryMenu = $showInventory || $showGoodsReceipts || $showGoodsReturns || $showMaterialIssuance;
    
    // Active state checks
    $purchasingActive = request()->routeIs('purchase-requests.*') ||
        request()->routeIs('quotations.*') ||
        request()->routeIs('purchase-orders.*');
    
    $inventoryActive = request()->routeIs('inventory.*') ||
        request()->routeIs('goods-receipts.*') ||
        request()->routeIs('goods-returns.*') ||
        request()->routeIs('material-issuance.*');
@endphp

<nav id="sidebar" class="sidebar collapse d-md-block">
    <div class="sidebar-inner pt-3 d-flex flex-column h-100">
        <div class="text-center text-white mb-4">
            <div class="sidebar-logo p-3 rounded-4 shadow-sm mx-auto">
                <img src="{{ asset('images/davao.png') }}" alt="Davao Modern Glass" class="img-fluid sidebar-logo-img">
            </div>
        </div>
        
        <ul class="nav flex-column mb-4">
            @if($showDashboard)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            @endif
            
            @if($showProjects)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('projects.index') || request()->routeIs('projects.create') || request()->routeIs('projects.edit') || request()->routeIs('projects.show') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                    <i class="bi bi-folder"></i> Projects
                </a>
            </li>
            @endif
            
            @if($showProjects && $user && ($user->isAdmin() || $user->hasRole('project_manager')))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('projects.completed') ? 'active' : '' }}" href="{{ route('projects.completed') }}">
                    <i class="bi bi-check-circle"></i> Completed Projects
                </a>
            </li>
            @endif
            
            @if($showChangeOrders)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('change-orders.*') ? 'active' : '' }}" href="{{ route('change-orders.index') }}">
                    <i class="bi bi-arrow-repeat"></i> Change Orders
                </a>
            </li>
            @endif
            
            @if($showPurchasingMenu)
            <li class="nav-item">
                <button class="nav-link nav-link-group {{ $purchasingActive ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#purchasingMenu" aria-expanded="{{ $purchasingActive ? 'true' : 'false' }}">
                    <span><i class="bi bi-bag-check me-2"></i>Purchasing</span>
                    <i class="bi bi-chevron-down ms-2"></i>
                </button>
                <div class="collapse {{ $purchasingActive ? 'show' : '' }}" id="purchasingMenu">
                    <ul class="nav flex-column sub-nav">
                        @if($showPurchaseRequests)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('purchase-requests.*') ? 'active' : '' }}" href="{{ route('purchase-requests.index') }}">
                                <i class="bi bi-file-earmark-text"></i> Purchase Requests
                            </a>
                        </li>
                        @endif
                        @if($showQuotations)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}" href="{{ route('quotations.index') }}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Quotations
                            </a>
                        </li>
                        @endif
                        @if($showPurchaseOrders)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">
                                <i class="bi bi-cart-check"></i> Purchase Orders
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            
            @if($showInventoryMenu)
            <li class="nav-item">
                <button class="nav-link nav-link-group {{ $inventoryActive ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#inventoryMenu" aria-expanded="{{ $inventoryActive ? 'true' : 'false' }}">
                    <span><i class="bi bi-boxes me-2"></i>Inventory Management</span>
                    <i class="bi bi-chevron-down ms-2"></i>
                </button>
                <div class="collapse {{ $inventoryActive ? 'show' : '' }}" id="inventoryMenu">
                    <ul class="nav flex-column sub-nav">
                        @if($showInventory)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                                <i class="bi bi-list-ul"></i> Goods List
                            </a>
                        </li>
                        @endif
                        @if($showGoodsReceipts)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('goods-receipts.*') ? 'active' : '' }}" href="{{ route('goods-receipts.index') }}">
                                <i class="bi bi-box-arrow-in-down"></i> Goods Receipts
                            </a>
                        </li>
                        @endif
                        @if($showGoodsReturns)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('goods-returns.*') ? 'active' : '' }}" href="{{ route('goods-returns.index') }}">
                                <i class="bi bi-box-arrow-up"></i> Goods Returns
                            </a>
                        </li>
                        @endif
                        @if($showMaterialIssuance)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('material-issuance.*') ? 'active' : '' }}" href="{{ route('material-issuance.index') }}">
                                <i class="bi bi-box-arrow-right"></i> Goods Issue
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            
            @if($showSuppliers)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <i class="bi bi-truck"></i> Suppliers
                </a>
            </li>
            @endif
            
            @if($showReports)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
            </li>
            @endif
            
            @if($showUsers)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            @endif
            @if($showAuditLogs)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                    <i class="bi bi-journal-text"></i> Audit Logs
                </a>
            </li>
            @endif
        </ul>

        <div class="sidebar-footer mt-auto text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="sidebar-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size: 1rem;">{{ auth()->user()->name ?? 'User' }}</div>
                        <small class="text-white-50" style="font-size: 0.9375rem;">{{ auth()->user()->email ?? 'user@example.com' }}</small>
                        @if(auth()->user() && auth()->user()->role)
                        <div class="mt-1">
                            <span class="badge bg-primary" style="font-size: 0.875rem;">{{ auth()->user()->role->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle px-3" type="button" id="sidebarAccountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="sidebarAccountDropdown">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 320px;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #0f172a 100%);
    color: #e2e8f0;
    box-shadow: 4px 0 24px rgba(15, 23, 42, 0.45);
    z-index: 1050;
    overflow-y: auto;
    overflow-x: hidden;
    transition: left 0.3s ease;
    display: flex;
    flex-direction: column;
}

/* Custom scrollbar for sidebar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

@media (min-width: 768px) {
    .sidebar {
        width: 320px;
    }
}

@media (min-width: 992px) {
    .sidebar {
        width: 320px;
    }
}

.sidebar-inner {
    display: flex;
    flex-direction: column;
    min-height: 100%;
    padding: 1.5rem 1rem 1rem 1rem;
    flex: 1;
}
.nav-link {
    color: #cbd5f5;
    padding: 0.875rem 1.25rem;
    border-radius: 12px;
    transition: all 0.2s ease;
    font-weight: 500;
    margin-bottom: 0.4rem;
    font-size: 1rem; /* 18px - increased */
}
.nav-link-group {
    width: 100%;
    text-align: left;
    border: none;
    background: transparent;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.nav-link-group i:last-child {
    transition: transform 0.2s ease;
}
.nav-link-group[aria-expanded="true"] i:last-child {
    transform: rotate(180deg);
}
.sub-nav {
    padding-left: 0.5rem;
    margin-top: 0.35rem;
}
.sub-nav .nav-link {
    font-size: 0.9375rem; /* 16.875px - increased */
    padding: 0.7rem 1.25rem 0.7rem 2.5rem;
    border-radius: 10px;
    color: #e2e8f0;
}
.sub-nav .nav-link i {
    font-size: 1rem; /* 18px - increased */
}
.nav-link i {
    margin-right: 0.75rem;
    font-size: 1.125rem; /* 20.25px - increased */
    width: 20px;
    text-align: center;
}
.nav-link:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
    transform: translateX(4px);
}
.nav-link.active {
    color: #ffffff;
    font-weight: 600;
    background: rgba(59, 130, 246, 0.25);
    border-left: 3px solid #3b82f6;
    transform: translateX(4px);
    box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.15);
}
.sidebar-footer {
    padding-top: 1.5rem;
    padding-bottom: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    margin-bottom: 0.75rem;
}
.sidebar-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem; /* 20.25px - increased */
}
.sidebar-footer .dropdown-toggle::after {
    display: none;
}

.sidebar-logo {
    width: 120px;
    height: 120px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    backdrop-filter: blur(12px);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.sidebar-logo::after {
    content: '';
    position: absolute;
    inset: 3px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    pointer-events: none;
}
.sidebar-logo:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.4);
}
.sidebar-logo-img {
    max-width: 100px;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.35));
}
</style>
