<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ChangeOrderController;
use App\Http\Controllers\MaterialRequisitionController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\GoodsReturnController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MaterialIssuanceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;

// Authentication routes (if using Laravel Breeze/Jetstream, these would be auto-generated)
// For now, we'll add a simple login route
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $auditLogService = app(\App\Services\AuditLogService::class);

    try {
        if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->filled('remember'))) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $request->session()->regenerate();
            
            // Log successful login
            $auditLogService->logActionWithoutModel(
                'login',
                \App\Models\User::class,
                $user->id,
                "User {$user->name} logged in successfully from IP: {$request->ip()}",
                $user->id
            );
            
            return redirect()->intended('/');
        }
        
        // Log failed login attempt
        $auditLogService->logActionWithoutModel(
            'login_failed',
            \App\Models\User::class,
            0,
            "Failed login attempt with email: {$request->input('email')} from IP: {$request->ip()}",
            null
        );
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Login attempt failed: ' . $e->getMessage());
        
        // Check if it's a database/session issue
        if (str_contains($e->getMessage(), 'sessions') || str_contains($e->getMessage(), 'table')) {
            return back()->withErrors(['email' => 'Session storage error. Please run: php artisan migrate and php artisan config:clear']);
        }
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
})->name('login.post');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    $auditLogService = app(\App\Services\AuditLogService::class);
    
    // Log logout before logging out the user
    if (auth()->check()) {
        $user = auth()->user();
        $auditLogService->logActionWithoutModel(
            'logout',
            \App\Models\User::class,
            $user->id,
            "User {$user->name} logged out from IP: {$request->ip()}",
            $user->id
        );
    }
    
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard - All authenticated users can access
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Projects - Admin, Project Manager (full), others (read-only via controller)
    Route::get('projects/completed', [ProjectController::class, 'completed'])->name('projects.completed')
        ->middleware('role:admin,project_manager,inventory_manager,purchasing,warehouse_manager');
    Route::resource('projects', ProjectController::class)
        ->middleware('role:admin,project_manager,inventory_manager,purchasing,warehouse_manager');
    Route::post('projects/{project}/mark-as-done', [ProjectController::class, 'markAsDone'])
        ->name('projects.mark-as-done')
        ->middleware('role:admin,project_manager');
    Route::post('projects/{project}/cancel', [ProjectController::class, 'cancel'])
        ->name('projects.cancel')
        ->middleware('role:admin,project_manager');

    // Change Orders - Admin, Project Manager (full), others (read-only via controller)
    Route::resource('change-orders', ChangeOrderController::class)
        ->middleware('role:admin,project_manager,inventory_manager,purchasing,warehouse_manager');
    Route::post('change-orders/{changeOrder}/approve', [ChangeOrderController::class, 'approve'])
        ->name('change-orders.approve')
        ->middleware('role:admin');
    Route::post('change-orders/{changeOrder}/reject', [ChangeOrderController::class, 'reject'])
        ->name('change-orders.reject')
        ->middleware('role:admin');
    Route::post('change-orders/{changeOrder}/cancel', [ChangeOrderController::class, 'cancel'])
        ->name('change-orders.cancel')
        ->middleware('role:admin,project_manager');

    // Purchase Requests - Admin, Purchasing, Project Manager (full), others (read-only via controller)
    Route::resource('purchase-requests', MaterialRequisitionController::class)
        ->middleware('role:admin,purchasing,project_manager,inventory_manager,warehouse_manager');
    Route::post('purchase-requests/{purchaseRequest}/approve', [MaterialRequisitionController::class, 'approve'])
        ->name('purchase-requests.approve')
        ->middleware('role:admin');
    Route::post('purchase-requests/{purchaseRequest}/submit', [MaterialRequisitionController::class, 'submit'])
        ->name('purchase-requests.submit')
        ->middleware('role:admin,purchasing,project_manager');
    Route::post('purchase-requests/{purchaseRequest}/cancel', [MaterialRequisitionController::class, 'cancel'])
        ->name('purchase-requests.cancel')
        ->middleware('role:admin,purchasing,project_manager');

    // Quotations - Admin, Purchasing (full), others (read-only via controller)
    Route::resource('quotations', QuotationController::class)
        ->middleware('role:admin,purchasing,inventory_manager,project_manager,warehouse_manager');
    Route::get('quotations/compare', [QuotationController::class, 'compare'])
        ->name('quotations.compare')
        ->middleware('role:admin,purchasing');
    Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])
        ->name('quotations.accept')
        ->middleware('role:admin,purchasing');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])
        ->name('quotations.reject')
        ->middleware('role:admin,purchasing');
    Route::post('quotations/{quotation}/cancel', [QuotationController::class, 'cancel'])
        ->name('quotations.cancel')
        ->middleware('role:admin,purchasing');
    Route::get('api/supplier-prices', [QuotationController::class, 'getSupplierPrices'])
        ->name('api.supplier-prices')
        ->middleware('role:admin,purchasing');

    // Purchase Orders - Admin, Purchasing (full), others (read-only via controller)
    Route::get('purchase-orders/pending', [PurchaseOrderController::class, 'pending'])
        ->name('purchase-orders.pending')
        ->middleware('role:admin,purchasing,inventory_manager,project_manager,warehouse_manager');
    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->middleware('role:admin,purchasing,inventory_manager,project_manager,warehouse_manager');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
        ->name('purchase-orders.approve')
        ->middleware('role:admin');
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel')
        ->middleware('role:admin,purchasing');
    Route::get('purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])
        ->name('purchase-orders.print')
        ->middleware('role:admin,purchasing,inventory_manager,project_manager,warehouse_manager');

    // Goods Receipts - Admin, Inventory Manager (full), Warehouse Manager (approve only), others (read-only via controller)
    Route::resource('goods-receipts', GoodsReceiptController::class)
        ->middleware('role:admin,inventory_manager,warehouse_manager,purchasing,project_manager');
    Route::post('goods-receipts/{goodsReceipt}/approve', [GoodsReceiptController::class, 'approve'])
        ->name('goods-receipts.approve')
        ->middleware('role:admin,inventory_manager,warehouse_manager');
    Route::post('goods-receipts/{goodsReceipt}/cancel', [GoodsReceiptController::class, 'cancel'])
        ->name('goods-receipts.cancel')
        ->middleware('role:admin,inventory_manager,warehouse_manager');

    // Goods Returns - Admin, Inventory Manager, Warehouse Manager (full), others (read-only via controller)
    Route::resource('goods-returns', GoodsReturnController::class)
        ->middleware('role:admin,inventory_manager,warehouse_manager,purchasing,project_manager');
    Route::post('goods-returns/{goodsReturn}/approve', [GoodsReturnController::class, 'approve'])
        ->name('goods-returns.approve')
        ->middleware('role:admin,inventory_manager,warehouse_manager');
    Route::post('goods-returns/{goodsReturn}/cancel', [GoodsReturnController::class, 'cancel'])
        ->name('goods-returns.cancel')
        ->middleware('role:admin,inventory_manager,warehouse_manager');

    // Inventory - Admin, Inventory Manager (full), others (read-only via controller)
    Route::resource('inventory', InventoryController::class)
        ->middleware('role:admin,inventory_manager,purchasing,project_manager,warehouse_manager');
    Route::post('inventory/{inventoryItem}/adjust-stock', [InventoryController::class, 'adjustStock'])
        ->name('inventory.adjust-stock')
        ->middleware('role:admin,inventory_manager');

    // Material Issuance - Admin, Inventory Manager (full), others (read-only via controller)
    Route::resource('material-issuance', MaterialIssuanceController::class)
        ->middleware('role:admin,inventory_manager,purchasing,project_manager,warehouse_manager');
    Route::post('material-issuance/{materialIssuance}/approve', [MaterialIssuanceController::class, 'approve'])
        ->name('material-issuance.approve')
        ->middleware('role:admin,inventory_manager');
    Route::post('material-issuance/{materialIssuance}/issue', [MaterialIssuanceController::class, 'issue'])
        ->name('material-issuance.issue')
        ->middleware('role:admin,inventory_manager');
    Route::post('material-issuance/{materialIssuance}/cancel', [MaterialIssuanceController::class, 'cancel'])
        ->name('material-issuance.cancel')
        ->middleware('role:admin,inventory_manager');

    // Reports - Admin (full), others (limited via controller)
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index')
        ->middleware('role:admin,inventory_manager,purchasing,project_manager,warehouse_manager');
    Route::get('reports/inventory-movement', [ReportsController::class, 'inventoryMovement'])
        ->name('reports.inventory-movement')
        ->middleware('role:admin,inventory_manager,warehouse_manager');
    Route::get('reports/purchase-history', [ReportsController::class, 'purchaseHistory'])
        ->name('reports.purchase-history')
        ->middleware('role:admin,purchasing');
    Route::get('reports/project-consumption', [ReportsController::class, 'projectConsumption'])
        ->name('reports.project-consumption')
        ->middleware('role:admin,inventory_manager,project_manager');
    Route::get('reports/supplier-performance', [ReportsController::class, 'supplierPerformance'])
        ->name('reports.supplier-performance')
        ->middleware('role:admin,purchasing');

    // Suppliers - Admin, Purchasing (full), others (read-only via controller)
    Route::resource('suppliers', SupplierController::class)
        ->middleware('role:admin,purchasing,inventory_manager,project_manager,warehouse_manager');
    Route::post('suppliers/{supplier}/prices', [SupplierController::class, 'storePrice'])
        ->name('suppliers.prices.store')
        ->middleware('role:admin,purchasing');
    Route::put('suppliers/{supplier}/prices/{priceId}', [SupplierController::class, 'updatePrice'])
        ->name('suppliers.prices.update')
        ->middleware('role:admin,purchasing');
    Route::delete('suppliers/{supplier}/prices/{priceId}', [SupplierController::class, 'deletePrice'])
        ->name('suppliers.prices.delete')
        ->middleware('role:admin,purchasing');

    // Users - Admin only (full)
    Route::resource('users', UserController::class)
        ->middleware('role:admin');
    Route::post('users/{user}/cancel', [UserController::class, 'cancel'])
        ->name('users.cancel')
        ->middleware('role:admin');

    // Audit Logs - Admin only
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index')
        ->middleware('role:admin');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])
        ->name('audit-logs.show')
        ->middleware('role:admin');
});
