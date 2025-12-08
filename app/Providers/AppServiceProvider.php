<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\AuditLogObserver;
use App\Models\Project;
use App\Models\ChangeOrder;
use App\Models\InventoryItem;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\GoodsReturn;
use App\Models\MaterialIssuance;
use App\Models\Supplier;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Load helper functions
        require_once app_path('helpers.php');
        require_once app_path('Helpers/AuditLogHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers for all models that need audit logging
        // Laravel will automatically resolve the observer with dependency injection
        Project::observe(AuditLogObserver::class);
        ChangeOrder::observe(AuditLogObserver::class);
        InventoryItem::observe(AuditLogObserver::class);
        PurchaseRequest::observe(AuditLogObserver::class);
        Quotation::observe(AuditLogObserver::class);
        PurchaseOrder::observe(AuditLogObserver::class);
        GoodsReceipt::observe(AuditLogObserver::class);
        GoodsReturn::observe(AuditLogObserver::class);
        MaterialIssuance::observe(AuditLogObserver::class);
        Supplier::observe(AuditLogObserver::class);
        User::observe(AuditLogObserver::class);
    }
}
