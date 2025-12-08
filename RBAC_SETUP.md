# Role-Based Access Control (RBAC) Setup

This document describes the role-based access control system that has been implemented in the ERP system.

## Overview

The system now includes comprehensive role-based access control (RBAC) that restricts access to different modules based on user roles. The access matrix follows the specifications in `ACCESS_MATRIX.md`.

## User Accounts Created

The following user accounts have been created for testing. All accounts use the default password: **`password`**

| Role | Email | Name | Password |
|------|-------|------|----------|
| Admin | admin@erp.com | Admin User | password |
| Inventory Manager | inventory@erp.com | Inventory Manager | password |
| Purchasing | purchasing@erp.com | Purchasing Officer | password |
| Project Manager | pm@erp.com | Project Manager | password |
| Warehouse Manager | warehouse@erp.com | Warehouse Manager | password |

## Role Permissions Summary

### Admin
- **Full access** to all modules
- Can manage users, approve workflows, and access all reports
- No restrictions

### Inventory Manager
- **Full access**: Inventory, Goods Receipts, Goods Returns, Material Issuance
- **Read-only**: Projects, Change Orders, Purchase Requests, Quotations, Purchase Orders, Suppliers
- **Limited reports**: Inventory movement and project consumption reports only

### Purchasing
- **Full access**: Purchase Requests, Quotations, Purchase Orders, Suppliers
- **Read-only**: Projects, Change Orders, Goods Receipts, Goods Returns, Inventory, Material Issuance
- **Limited reports**: Purchase history and supplier performance reports only
- **Note**: Cannot approve Purchase Requests or Purchase Orders (only Admin can approve)

### Project Manager
- **Full access**: Projects (own projects), Change Orders (own projects), Purchase Requests (own projects)
- **Read-only**: Quotations, Purchase Orders, Goods Receipts, Goods Returns, Inventory, Material Issuance, Suppliers
- **Limited reports**: Project consumption report for own projects only

### Warehouse Manager
- **Limited access**: Goods Receipts (can approve/reject for quality inspection)
- **Full access**: Goods Returns
- **Read-only**: All other modules
- **Limited reports**: Inventory movement report for quality tracking

## Security Features

### Middleware Protection
All routes are protected by role-based middleware that checks user roles before allowing access. The middleware is defined in `app/Http/Middleware/RoleMiddleware.php`.

### Route Protection
Routes are protected at multiple levels:
1. **Authentication**: All routes require user authentication
2. **Role-based**: Routes check for specific roles
3. **Controller-level**: Additional permission checks can be added in controllers for fine-grained control

### User Model Helpers
The `User` model includes helper methods for role checking:
- `hasRole(string $roleSlug)`: Check if user has a specific role
- `hasAnyRole(array $roleSlugs)`: Check if user has any of the given roles
- `isAdmin()`: Check if user is admin
- `canAccessModule(string $module)`: Check if user can access a module
- `getRolePermissions()`: Get all permissions for the user's role

## Running the Seeder

To create the roles and user accounts, run:

```bash
php artisan db:seed --class=DatabaseSeeder
```

Or to refresh the database and seed:

```bash
php artisan migrate:fresh --seed
```

## Testing Access Control

1. Log in with each user account to test access restrictions
2. Try accessing routes that should be restricted for each role
3. Verify that:
   - Read-only users can view but not edit/delete/create
   - Approval actions are only available to authorized roles
   - Module access matches the access matrix

## Customizing Permissions

To modify permissions:

1. **Update Access Matrix**: Edit `ACCESS_MATRIX.md` to reflect new permission requirements
2. **Update User Model**: Modify the `getRolePermissions()` method in `app/Models/User.php`
3. **Update Routes**: Adjust role middleware on routes in `routes/web.php`
4. **Update Controllers**: Add additional permission checks in controllers if needed

## Notes

- The default password for all seeded users is `password` - **change these in production**
- Admin role has full access to everything and bypasses all restrictions
- Project Managers can only fully manage their own projects (additional controller logic may be needed)
- Approval workflows typically require Admin role, except for:
  - Goods Receipts: Can be approved by Inventory Manager or Quality Control
  - Goods Returns: Can be approved by Inventory Manager or Quality Control

## File Changes Made

1. **Created**: `app/Http/Middleware/RoleMiddleware.php` - Role-based access middleware
2. **Modified**: `app/Models/User.php` - Added role helper methods
3. **Modified**: `bootstrap/app.php` - Registered role middleware
4. **Modified**: `routes/web.php` - Applied role middleware to all routes
5. **Modified**: `database/seeders/DatabaseSeeder.php` - Added user creation for all roles

