# Navigation Visibility Rules

This document explains how modules are shown/hidden in the navigation menu based on user roles, following the **Golden Rule**: *"If a user can't perform any action in a module, they shouldn't see it in navigation. If they need reference data, show it contextually or in a filtered view."*

## Implementation

The navigation visibility is controlled by the `shouldShowModuleInNavigation()` method in the `User` model, which implements role-specific rules.

## Role-Based Navigation Visibility

### Admin
**Shows:** Everything (all modules visible)
- Has full access to all modules, so all navigation items are visible

### Inventory Manager
**Shows:**
- Dashboard ✓
- Inventory Management:
  - Goods List (Inventory) ✓
  - Goods Receipts ✓
  - Goods Returns ✓
  - Goods Issue (Material Issuance) ✓
- Reports ✓

**Hidden:**
- Projects (project selection happens contextually in Material Issuance form)
- Change Orders (not operationally essential)
- Purchase Requests (not operationally essential)
- Quotations (not operationally essential)
- Purchase Orders (shown contextually when creating Goods Receipts)
- Suppliers (shown contextually in Goods Receipts/Returns)
- Users (no access)

### Purchasing Officer
**Shows:**
- Dashboard ✓
- Purchasing:
  - Purchase Requests ✓
  - Quotations ✓
  - Purchase Orders ✓
- Suppliers ✓
- Reports ✓

**Hidden:**
- Projects (project selection happens contextually in Purchase Requests)
- Change Orders (not operationally essential)
- Inventory Management (read-only access, not essential)
- Users (no access)

### Project Manager
**Shows:**
- Dashboard ✓
- Projects ✓
  - Completed Projects ✓
- Change Orders ✓
- Purchasing:
  - Purchase Requests ✓
- Reports ✓

**Hidden:**
- Quotations (read-only, not essential)
- Purchase Orders (read-only, not essential)
- Inventory Management (read-only, not essential)
- Suppliers (read-only, not essential)
- Users (no access)

### Warehouse Manager
**Shows:**
- Dashboard ✓
- Inventory Management:
  - Goods Receipts (for quality inspection) ✓
  - Goods Returns ✓
- Reports ✓

**Hidden:**
- Projects (not essential)
- Change Orders (not essential)
- Purchase Requests (not essential)
- Quotations (not essential)
- Purchase Orders (shown contextually when processing Goods Receipts)
- Inventory/Goods List (read-only, not essential)
- Material Issuance (read-only, not essential)
- Suppliers (shown contextually in Goods Receipts/Returns)
- Users (no access)

## Key Principles Applied

1. **Action-Based Visibility**: Only show modules where users can perform actions (create, edit, approve, etc.)
2. **Contextual Data Access**: Reference data (like Projects, Suppliers) is shown within forms/workflows where needed, not as standalone navigation items
3. **Operational Relevance**: Hide modules that aren't essential for daily operations, even if read-only access exists
4. **Reduced Clutter**: Cleaner navigation = better user experience and focus

## Benefits

✅ **Cleaner Interface**: Users only see what they need  
✅ **Faster Navigation**: Less menu items to scan  
✅ **Better Focus**: Users aren't distracted by irrelevant options  
✅ **Improved Security**: Fewer visible entry points  
✅ **Clear Role Definition**: Navigation clearly shows role responsibilities  

## Technical Details

- Method: `User::shouldShowModuleInNavigation(string $module): bool`
- Location: `app/Models/User.php`
- Navigation Template: `resources/views/layouts/sidebar.blade.php`
- Route protection still enforced: Hidden modules are still protected by middleware (defense in depth)

## Customization

To modify navigation visibility for a role, edit the `navigationRules` array in the `shouldShowModuleInNavigation()` method in `User.php`.

