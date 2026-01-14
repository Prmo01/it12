<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\InventoryItem;
use App\Models\MaterialIssuance;
use App\Models\GoodsReceipt;
use App\Models\GoodsReturn;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user || !$user->role) {
            return redirect()->route('login');
        }

        $roleSlug = $user->role->slug;

        // Get date range from request or use defaults
        $dateFrom = $request->input('date_from', now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Route to role-specific dashboard method
        switch ($roleSlug) {
            case 'admin':
                return $this->adminDashboard($dateFrom, $dateTo);
            case 'inventory_manager':
                return $this->inventoryManagerDashboard($dateFrom, $dateTo);
            case 'purchasing':
                return $this->purchasingDashboard($dateFrom, $dateTo);
            case 'project_manager':
                return $this->projectManagerDashboard($dateFrom, $dateTo);
            case 'warehouse_manager':
                return $this->warehouseManagerDashboard($dateFrom, $dateTo);
            default:
                // Fallback to admin dashboard for unknown roles
                return $this->adminDashboard($dateFrom, $dateTo);
        }
    }

    /**
     * Admin Dashboard - Full access to all data
     */
    protected function adminDashboard($dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
        $dateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $totalProjects = Project::where('status', '!=', 'completed')->count();
        $activeProjects = Project::where('status', 'active')->count();
        $pendingPOs = PurchaseOrder::whereIn('status', ['draft', 'pending'])->count();
        $lowStockItems = InventoryItem::get()->filter(function ($item) {
            return $this->stockService->checkReorderLevel($item->id);
        })->count();

        $recentProjects = Project::latest()->take(5)->get();
        $recentPOs = PurchaseOrder::with('supplier')->latest()->take(5)->get();
        $recentMaterialIssuances = MaterialIssuance::with('project')->latest()->take(5)->get();

        $projectStatusData = Project::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $poStatusData = PurchaseOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Monthly POs - use chart-specific date or global date
        $poDateFrom = request('po_date_from') ? \Carbon\Carbon::parse(request('po_date_from'))->startOfDay() : $dateFrom;
        $poDateTo = request('po_date_to') ? \Carbon\Carbon::parse(request('po_date_to'))->endOfDay() : $dateTo;
        $monthlyPOs = PurchaseOrder::selectRaw($this->getDateFormatFunction('created_at') . " as month, count(*) as count")
            ->whereBetween('created_at', [$poDateFrom, $poDateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Monthly Projects - use chart-specific date or global date
        $projectsDateFrom = request('projects_date_from') ? \Carbon\Carbon::parse(request('projects_date_from'))->startOfDay() : $dateFrom;
        $projectsDateTo = request('projects_date_to') ? \Carbon\Carbon::parse(request('projects_date_to'))->endOfDay() : $dateTo;
        $monthlyProjects = Project::selectRaw($this->getDateFormatFunction('created_at') . " as month, count(*) as count")
            ->whereBetween('created_at', [$projectsDateFrom, $projectsDateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $inventoryMovements = $this->getInventoryMovements($dateFrom, $dateTo);
        $topSuppliers = $this->getTopSuppliers();

        return view('dashboards.admin', compact(
            'totalProjects',
            'activeProjects',
            'pendingPOs',
            'lowStockItems',
            'recentProjects',
            'recentPOs',
            'recentMaterialIssuances',
            'projectStatusData',
            'poStatusData',
            'monthlyPOs',
            'monthlyProjects',
            'inventoryMovements',
            'topSuppliers',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Inventory Manager Dashboard - Focus on inventory operations
     */
    protected function inventoryManagerDashboard($dateFrom = null, $dateTo = null)
    {
        $totalItems = InventoryItem::count();
        $lowStockItems = InventoryItem::get()->filter(function ($item) {
            return $this->stockService->checkReorderLevel($item->id);
        })->count();
        
        $pendingReceipts = GoodsReceipt::where('status', 'pending')->count();
        $pendingReturns = GoodsReturn::where('status', 'pending')->count();
        
        $recentReceipts = GoodsReceipt::with('purchaseOrder.supplier')->latest()->take(5)->get();
        $recentReturns = GoodsReturn::with('goodsReceipt.purchaseOrder.supplier')->latest()->take(5)->get();
        $recentIssuances = MaterialIssuance::with('project')->latest()->take(5)->get();

        $dateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
        $dateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $inventoryMovements = $this->getInventoryMovements($dateFrom, $dateTo);
        
        $receiptStatusData = GoodsReceipt::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $dateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
        $dateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        // Monthly Receipts - use chart-specific date or global date
        $receiptsDateFrom = request('receipts_date_from') ? \Carbon\Carbon::parse(request('receipts_date_from'))->startOfDay() : $dateFrom;
        $receiptsDateTo = request('receipts_date_to') ? \Carbon\Carbon::parse(request('receipts_date_to'))->endOfDay() : $dateTo;
        $monthlyReceipts = GoodsReceipt::selectRaw($this->getDateFormatFunction('created_at') . " as month, count(*) as count")
            ->whereBetween('created_at', [$receiptsDateFrom, $receiptsDateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Monthly Issuances - use chart-specific date or global date
        $issuancesDateFrom = request('issuances_date_from') ? \Carbon\Carbon::parse(request('issuances_date_from'))->startOfDay() : $dateFrom;
        $issuancesDateTo = request('issuances_date_to') ? \Carbon\Carbon::parse(request('issuances_date_to'))->endOfDay() : $dateTo;
        $monthlyIssuances = MaterialIssuance::selectRaw($this->getDateFormatFunction('created_at') . " as month, count(*) as count")
            ->whereBetween('created_at', [$issuancesDateFrom, $issuancesDateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        return view('dashboards.inventory_manager', compact(
            'totalItems',
            'lowStockItems',
            'pendingReceipts',
            'pendingReturns',
            'recentReceipts',
            'recentReturns',
            'recentIssuances',
            'inventoryMovements',
            'receiptStatusData',
            'monthlyReceipts',
            'monthlyIssuances',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Purchasing Dashboard - Focus on procurement
     */
    protected function purchasingDashboard($dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
        $dateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $pendingPOs = PurchaseOrder::where('status', 'pending')->count();
        $pendingPRs = PurchaseRequest::where('status', 'pending')->count();
        $totalSuppliers = \App\Models\Supplier::count();
        $activeQuotations = \App\Models\Quotation::where('status', 'pending')->count();

        $recentPOs = PurchaseOrder::with('supplier')->latest()->take(5)->get();
        $recentPRs = PurchaseRequest::with('project')->latest()->take(5)->get();
        $recentQuotations = \App\Models\Quotation::with('supplier')->latest()->take(5)->get();

        $poStatusData = PurchaseOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $prStatusData = PurchaseRequest::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Monthly POs - use chart-specific date or global date
        $poDateFrom = request('po_date_from') ? \Carbon\Carbon::parse(request('po_date_from'))->startOfDay() : $dateFrom;
        $poDateTo = request('po_date_to') ? \Carbon\Carbon::parse(request('po_date_to'))->endOfDay() : $dateTo;
        $monthlyPOs = PurchaseOrder::selectRaw($this->getDateFormatFunction('created_at') . " as month, count(*) as count")
            ->whereBetween('created_at', [$poDateFrom, $poDateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $topSuppliers = $this->getTopSuppliers();

        return view('dashboards.purchasing', compact(
            'pendingPOs',
            'pendingPRs',
            'totalSuppliers',
            'activeQuotations',
            'recentPOs',
            'recentPRs',
            'recentQuotations',
            'poStatusData',
            'prStatusData',
            'monthlyPOs',
            'topSuppliers',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Project Manager Dashboard - Focus on projects
     */
    protected function projectManagerDashboard($dateFrom = null, $dateTo = null)
    {
        $user = auth()->user();
        
        $myProjects = Project::where('project_manager_id', $user->id)
            ->where('status', '!=', 'completed')->count();
        $activeProjects = Project::where('project_manager_id', $user->id)
            ->where('status', 'active')->count();
        $pendingChangeOrders = \App\Models\ChangeOrder::whereHas('project', function($q) use ($user) {
            $q->where('project_manager_id', $user->id);
        })->where('status', 'pending')->count();
        $pendingPRs = PurchaseRequest::whereHas('project', function($q) use ($user) {
            $q->where('project_manager_id', $user->id);
        })->where('status', 'pending')->count();

        $recentProjects = Project::where('project_manager_id', $user->id)->latest()->take(5)->get();
        $recentChangeOrders = \App\Models\ChangeOrder::whereHas('project', function($q) use ($user) {
            $q->where('project_manager_id', $user->id);
        })->with('project')->latest()->take(5)->get();
        $recentPRs = PurchaseRequest::whereHas('project', function($q) use ($user) {
            $q->where('project_manager_id', $user->id);
        })->with('project')->latest()->take(5)->get();

        $projectStatusData = Project::where('project_manager_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $dateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
        $dateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        // Monthly Projects - use chart-specific date or global date
        $projectsDateFrom = request('projects_date_from') ? \Carbon\Carbon::parse(request('projects_date_from'))->startOfDay() : $dateFrom;
        $projectsDateTo = request('projects_date_to') ? \Carbon\Carbon::parse(request('projects_date_to'))->endOfDay() : $dateTo;
        $monthlyProjects = Project::where('project_manager_id', $user->id)
            ->selectRaw($this->getDateFormatFunction('created_at') . " as month, count(*) as count")
            ->whereBetween('created_at', [$projectsDateFrom, $projectsDateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        return view('dashboards.project_manager', compact(
            'myProjects',
            'activeProjects',
            'pendingChangeOrders',
            'pendingPRs',
            'recentProjects',
            'recentChangeOrders',
            'recentPRs',
            'projectStatusData',
            'monthlyProjects',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Warehouse Manager Dashboard - Focus on warehouse operations and quality inspection
     */
    protected function warehouseManagerDashboard($dateFrom = null, $dateTo = null)
    {
        $pendingInspections = GoodsReceipt::where('status', 'pending')->count();
        $pendingReturns = GoodsReturn::where('status', 'pending')->count();
        $approvedToday = GoodsReceipt::whereDate('approved_at', today())->count();
        $rejectedToday = GoodsReceipt::where('status', 'rejected')
            ->whereDate('rejected_at', today())
            ->count();
        $approvedPOsReady = PurchaseOrder::where('status', 'approved')
            ->whereDoesntHave('goodsReceipts', function ($q) {
                $q->where('status', 'approved');
            })
            ->count();

        $recentReceipts = GoodsReceipt::with('purchaseOrder.supplier')->latest()->take(5)->get();
        $recentReturns = GoodsReturn::with('goodsReceipt.purchaseOrder.supplier')->latest()->take(5)->get();

        $receiptStatusData = GoodsReceipt::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $dateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay();
        $dateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        // Monthly Approvals - use chart-specific date or global date
        $approvalsDateFrom = request('approvals_date_from') ? \Carbon\Carbon::parse(request('approvals_date_from'))->startOfDay() : ($dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subMonths(6)->startOfDay());
        $approvalsDateTo = request('approvals_date_to') ? \Carbon\Carbon::parse(request('approvals_date_to'))->endOfDay() : ($dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay());
        $monthlyApprovals = GoodsReceipt::selectRaw($this->getDateFormatFunction('approved_at') . " as month, count(*) as count")
            ->whereNotNull('approved_at')
            ->whereBetween('approved_at', [$approvalsDateFrom, $approvalsDateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Inventory Movements - use chart-specific date or global date
        $inventoryDateFrom = request('inventory_date_from') ? \Carbon\Carbon::parse(request('inventory_date_from'))->startOfDay() : ($dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subDays(30)->startOfDay());
        $inventoryDateTo = request('inventory_date_to') ? \Carbon\Carbon::parse(request('inventory_date_to'))->endOfDay() : ($dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay());
        $inventoryMovements = $this->getInventoryMovements($inventoryDateFrom, $inventoryDateTo);

        return view('dashboards.warehouse_manager', compact(
            'pendingInspections',
            'pendingReturns',
            'approvedToday',
            'rejectedToday',
            'approvedPOsReady',
            'recentReceipts',
            'recentReturns',
            'receiptStatusData',
            'monthlyApprovals',
            'inventoryMovements',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Helper method to get inventory movements
     */
    protected function getInventoryMovements($dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subDays(30)->startOfDay();
        $dateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $inventoryMovementsRaw = \App\Models\StockMovement::selectRaw("DATE(created_at) as date, movement_type, SUM(quantity) as total")
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date', 'movement_type')
            ->orderBy('date')
            ->get();
        
        $inventoryMovements = [];
        foreach ($inventoryMovementsRaw as $movement) {
            $date = $movement->date;
            if (!isset($inventoryMovements[$date])) {
                $inventoryMovements[$date] = [];
            }
            $inventoryMovements[$date][] = [
                'movement_type' => $movement->movement_type,
                'total' => $movement->total
            ];
        }

        return $inventoryMovements;
    }

    /**
     * Helper method to get top suppliers
     */
    protected function getTopSuppliers()
    {
        return PurchaseOrder::selectRaw('supplier_id, count(*) as order_count, SUM(total_amount) as total_amount')
            ->whereNotNull('supplier_id')
            ->groupBy('supplier_id')
            ->with('supplier')
            ->orderByDesc('order_count')
            ->take(5)
            ->get();
    }

    /**
     * Helper method to get database-agnostic date format function
     * Returns the appropriate SQL function based on the database driver
     */
    protected function getDateFormatFunction($column = 'created_at')
    {
        try {
            // Try to get driver from active connection (most reliable)
            $pdo = DB::connection()->getPdo();
            $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Exception $e) {
            // Fallback to config
            $connectionName = config('database.default');
            $driver = config("database.connections.{$connectionName}.driver", 'mysql');
        }
        
        // PostgreSQL uses pgsql as driver name
        if ($driver === 'pgsql') {
            return "TO_CHAR({$column}, 'YYYY-MM')";
        } else {
            // MySQL, SQLite, etc.
            return "DATE_FORMAT({$column}, '%Y-%m')";
        }
    }
}

