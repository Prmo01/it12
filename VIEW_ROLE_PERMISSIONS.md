# View Role-Based Permissions Implementation

This document describes how role-based permissions have been implemented in the views to show/hide content based on user roles.

## Overview

All views have been updated to conditionally display content based on user role permissions. Users will only see:
- Menu items they have access to
- Dashboard sections relevant to their role
- Action buttons they're authorized to use

## Implementation Details

### 1. Sidebar Navigation (`resources/views/layouts/sidebar.blade.php`)

The sidebar now conditionally displays menu items based on user permissions:

- **Dashboard**: Visible to all authenticated users
- **Projects**: Only shown if user has `projects` module access
- **Change Orders**: Only shown if user has `change_orders` module access
- **Purchasing Menu**: Only shown if user has access to any purchasing module
  - Purchase Requests: Shown based on `purchase_requests` permission
  - Quotations: Shown based on `quotations` permission
  - Purchase Orders: Shown based on `purchase_orders` permission
- **Inventory Menu**: Only shown if user has access to any inventory module
  - Goods List: Shown based on `inventory` permission
  - Goods Receipts: Shown based on `goods_receipts` permission
  - Goods Returns: Shown based on `goods_returns` permission
  - Material Issuance: Shown based on `material_issuance` permission
- **Suppliers**: Only shown if user has `suppliers` module access
- **Reports**: Only shown if user has `reports` module access
- **Users**: Only shown if user has `users` module access (Admin or Viewer)

The sidebar footer also displays the user's role badge.

### 2. Dashboard (`resources/views/dashboard.blade.php`)

The dashboard conditionally shows:

**Stat Cards:**
- Total/Active Projects: Only shown if user has `projects` access
- Pending Purchase Orders: Only shown if user has `purchase_orders` access
- Low Stock Items: Only shown if user has `inventory` access

**Charts:**
- Projects by Status: Only rendered if user has `projects` access
- Purchase Orders by Status: Only rendered if user has `purchase_orders` access
- Monthly Trends: Conditionally shown based on relevant module access
- Inventory Movements: Only shown if user has `inventory` access
- Top Suppliers: Only shown if user has both `purchase_orders` and `reports` access

**Recent Activities:**
- Recent Projects: Only shown if user has `projects` access
- Recent Purchase Orders: Only shown if user has `purchase_orders` access
- Recent Material Issuances: Only shown if user has `material_issuance` access

### 3. Individual View Files

Example: Projects Index (`resources/views/projects/index.blade.php`)

- **Create Button**: Only shown to Admin and Project Manager roles
- **Completed Projects Link**: Only shown to Admin and Project Manager roles
- **Edit Button**: Only shown to Admin and Project Manager roles
- **View Button**: Always shown (read access)

## Permission Checking in Views

Views use the User model's helper methods to check permissions:

```php
@php
    $user = auth()->user();
    $permissions = $user ? $user->getRolePermissions() : [];
    $canAccessModule = $user && $user->canAccessModule('module_name');
    $canCreate = $user && ($user->isAdmin() || $user->hasRole('specific_role'));
@endphp

@if($canAccessModule)
    <!-- Content visible to users with access -->
@endif
```

## User Model Helper Methods

The following methods are available in views:

- `$user->canAccessModule(string $module)`: Check if user can access a module
- `$user->hasRole(string $roleSlug)`: Check if user has a specific role
- `$user->hasAnyRole(array $roleSlugs)`: Check if user has any of the given roles
- `$user->isAdmin()`: Check if user is admin
- `$user->getRolePermissions()`: Get all permissions for user's role

## Permission Levels

Based on `ACCESS_MATRIX.md`:

1. **Full Access**: Can create, edit, delete, approve
2. **Read Only**: Can view but not modify
3. **Limited**: Specific actions only (e.g., approve only)
4. **No Access**: Cannot see the module at all

## Examples by Role

### Admin
- Sees all menu items
- Sees all dashboard sections
- Can perform all actions (create, edit, delete, approve)

### Inventory Manager
- Sees: Dashboard, Inventory, Goods Receipts, Goods Returns, Material Issuance, Reports (limited)
- Dashboard shows: Inventory stats, inventory movements, material issuances
- Can create/edit inventory items, receipts, returns, issuances

### Purchasing
- Sees: Dashboard, Purchasing menu (PRs, Quotations, POs), Suppliers, Reports (limited)
- Dashboard shows: Purchase order stats, recent POs, supplier charts
- Can create/edit PRs, quotations, POs, suppliers
- Cannot approve PRs or POs (only Admin can)

### Project Manager
- Sees: Dashboard, Projects, Change Orders, Purchase Requests
- Dashboard shows: Project stats, recent projects
- Can create/edit own projects, change orders, purchase requests
- Cannot approve change orders (only Admin can)

### Quality Control
- Sees: Dashboard, Goods Receipts (limited), Goods Returns
- Dashboard shows: Limited inventory stats
- Can approve/reject goods receipts (quality inspection)
- Can create/edit goods returns

### Viewer/Auditor
- Sees all menu items (read-only)
- Dashboard shows all sections (read-only)
- Cannot perform any create/edit/delete/approve actions
- All action buttons are hidden

## Extending to Other Views

To add permission checks to other views:

1. Check module access:
```php
@php
    $user = auth()->user();
    $canAccess = $user && $user->canAccessModule('module_name');
@endphp
```

2. Check for specific roles:
```php
@if($user && ($user->isAdmin() || $user->hasRole('specific_role')))
    <!-- Content for specific roles -->
@endif
```

3. Check permission level:
```php
@php
    $permissions = $user->getRolePermissions();
    $modulePermission = $permissions['module_name'] ?? 'no_access';
    $canEdit = $modulePermission === 'full_access';
@endphp

@if($canEdit)
    <!-- Edit button -->
@endif
```

## Testing

To test the role-based views:

1. Log in with each role account
2. Verify only relevant menu items appear
3. Check dashboard shows only relevant sections
4. Confirm action buttons are shown/hidden correctly
5. Verify read-only users cannot see create/edit/delete buttons

## Notes

- All permission checks use the User model methods which reference `ACCESS_MATRIX.md`
- Routes are still protected by middleware (backend security)
- Views provide UI-level security (hiding unauthorized actions)
- JavaScript charts are conditionally initialized based on element existence
- Role badge is displayed in sidebar footer for easy identification

