<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'cancellation_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    public function clientProjects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->role && in_array($this->role->slug, $roleSlugs);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user can access module based on access matrix
     */
    public function canAccessModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin has full access
        }

        if (!$this->role) {
            return false;
        }

        $permissions = $this->getRolePermissions();
        return isset($permissions[$module]) && $permissions[$module] !== 'no_access';
    }

    /**
     * Check if a module should be shown in navigation menu
     * Golden Rule: If user can't perform any action, hide it. If they need reference data, show it contextually.
     */
    public function shouldShowModuleInNavigation(string $module): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin sees everything
        }

        if (!$this->role) {
            return false;
        }

        $roleSlug = $this->role->slug;
        $permissions = $this->getRolePermissions();
        
        // Get permission level
        $permission = $permissions[$module] ?? 'no_access';
        
        // Navigation visibility rules per role (following Golden Rule)
        $navigationRules = [
            'inventory_manager' => [
                'dashboard' => true,
                'projects' => false, // Hidden - project selection happens contextually in Material Issuance
                'change_orders' => false, // Hidden - not essential
                'purchase_requests' => false, // Hidden - not essential
                'quotations' => false, // Hidden - not essential
                'purchase_orders' => false, // Hidden - not essential (they see it when creating Goods Receipts)
                'goods_receipts' => true, // Shown - full access
                'goods_returns' => true, // Shown - full access
                'inventory' => true, // Shown - full access
                'material_issuance' => true, // Shown - full access
                'reports' => true, // Shown - limited access
                'suppliers' => false, // Hidden - not essential (shown contextually in Goods Receipts/Returns)
                'users' => false, // Hidden - no access
            ],
            'purchasing' => [
                'dashboard' => true,
                'projects' => false, // Hidden - project selection happens contextually in Purchase Requests
                'change_orders' => false, // Hidden - not essential
                'purchase_requests' => true, // Shown - full access
                'quotations' => true, // Shown - full access
                'purchase_orders' => true, // Shown - full access
                'goods_receipts' => false, // Hidden - read-only not essential
                'goods_returns' => false, // Hidden - read-only not essential
                'inventory' => false, // Hidden - read-only not essential (they see stock levels contextually)
                'material_issuance' => false, // Hidden - read-only not essential
                'reports' => true, // Shown - limited access
                'suppliers' => true, // Shown - full access
                'users' => false, // Hidden - no access
            ],
            'project_manager' => [
                'dashboard' => true,
                'projects' => true, // Shown - full access
                'change_orders' => true, // Shown - full access
                'purchase_requests' => true, // Shown - full access
                'quotations' => false, // Hidden - read-only not essential
                'purchase_orders' => false, // Hidden - read-only not essential
                'goods_receipts' => false, // Hidden - read-only not essential
                'goods_returns' => false, // Hidden - read-only not essential
                'inventory' => false, // Hidden - read-only not essential
                'material_issuance' => false, // Hidden - read-only not essential
                'reports' => true, // Shown - limited access
                'suppliers' => false, // Hidden - read-only not essential
                'users' => false, // Hidden - no access
            ],
            'warehouse_manager' => [
                'dashboard' => true,
                'projects' => false, // Hidden - not essential
                'change_orders' => false, // Hidden - not essential
                'purchase_requests' => false, // Hidden - not essential
                'quotations' => false, // Hidden - not essential
                'purchase_orders' => false, // Hidden - not essential (they see it when processing Goods Receipts)
                'goods_receipts' => true, // Shown - limited access (approve/reject)
                'goods_returns' => true, // Shown - full access
                'inventory' => false, // Hidden - read-only not essential
                'material_issuance' => false, // Hidden - read-only not essential
                'reports' => true, // Shown - limited access
                'suppliers' => false, // Hidden - shown contextually in Goods Receipts/Returns
                'users' => false, // Hidden - no access
            ],
        ];

        // Check if there's a specific navigation rule for this role and module
        if (isset($navigationRules[$roleSlug][$module])) {
            return $navigationRules[$roleSlug][$module];
        }

        // Default: Only show if user has full_access (can perform actions)
        return $permission === 'full_access';
    }

    /**
     * Get permissions for user's role based on ACCESS_MATRIX.md
     */
    public function getRolePermissions(): array
    {
        if (!$this->role) {
            return [];
        }

        $matrix = [
            'admin' => [
                'dashboard' => 'full_access',
                'projects' => 'full_access',
                'change_orders' => 'full_access',
                'purchase_requests' => 'full_access',
                'quotations' => 'full_access',
                'purchase_orders' => 'full_access',
                'goods_receipts' => 'full_access',
                'goods_returns' => 'full_access',
                'inventory' => 'full_access',
                'material_issuance' => 'full_access',
                'reports' => 'full_access',
                'suppliers' => 'full_access',
                'users' => 'full_access',
            ],
            'inventory_manager' => [
                'dashboard' => 'full_access',
                'projects' => 'read_only',
                'change_orders' => 'read_only',
                'purchase_requests' => 'read_only',
                'quotations' => 'read_only',
                'purchase_orders' => 'read_only',
                'goods_receipts' => 'full_access',
                'goods_returns' => 'full_access',
                'inventory' => 'full_access',
                'material_issuance' => 'full_access',
                'reports' => 'limited',
                'suppliers' => 'read_only',
                'users' => 'no_access',
            ],
            'purchasing' => [
                'dashboard' => 'full_access',
                'projects' => 'read_only',
                'change_orders' => 'read_only',
                'purchase_requests' => 'full_access',
                'quotations' => 'full_access',
                'purchase_orders' => 'full_access',
                'goods_receipts' => 'read_only',
                'goods_returns' => 'read_only',
                'inventory' => 'read_only',
                'material_issuance' => 'read_only',
                'reports' => 'limited',
                'suppliers' => 'full_access',
                'users' => 'no_access',
            ],
            'project_manager' => [
                'dashboard' => 'full_access',
                'projects' => 'full_access',
                'change_orders' => 'full_access',
                'purchase_requests' => 'full_access',
                'quotations' => 'read_only',
                'purchase_orders' => 'read_only',
                'goods_receipts' => 'read_only',
                'goods_returns' => 'read_only',
                'inventory' => 'read_only',
                'material_issuance' => 'read_only',
                'reports' => 'limited',
                'suppliers' => 'read_only',
                'users' => 'no_access',
            ],
            'warehouse_manager' => [
                'dashboard' => 'read_only',
                'projects' => 'read_only',
                'change_orders' => 'read_only',
                'purchase_requests' => 'read_only',
                'quotations' => 'read_only',
                'purchase_orders' => 'read_only',
                'goods_receipts' => 'limited',
                'goods_returns' => 'full_access',
                'inventory' => 'read_only',
                'material_issuance' => 'read_only',
                'reports' => 'limited',
                'suppliers' => 'read_only',
                'users' => 'no_access',
            ],
        ];

        return $matrix[$this->role->slug] ?? [];
    }
}
