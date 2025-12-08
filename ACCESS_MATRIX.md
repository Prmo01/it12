# Access Matrix by Role

## System Modules Overview
1. **Dashboard** - Overview and statistics
2. **Projects** - Project management, mark as done, completed projects
3. **Change Orders** - Project change order management
4. **Purchase Requests** - Material requisition requests
5. **Quotations** - Supplier quotations and comparison
6. **Purchase Orders** - Purchase order management and approval
7. **Goods Receipts** - Receiving goods into inventory
8. **Goods Returns** - Returning defective/unwanted goods
9. **Inventory** - Inventory item management and stock adjustments
10. **Material Issuance** - Issuing materials to projects/work orders
11. **Reports** - Various system reports
12. **Suppliers** - Supplier management and pricing
13. **Users** - User account management

---

## Access Matrix

### Legend:
- ✅ **Full Access** - Create, Read, Update, Delete, Approve/Reject
- 📖 **Read Only** - View only, no modifications
- ⚠️ **Limited** - Specific actions only (see notes)
- ❌ **No Access** - Cannot access this module

---

### 1. Admin
**Description:** Full system access - Can manage all modules, users, and system settings

| Module | Access Level | Permissions |
|--------|-------------|-------------|
| Dashboard | ✅ Full Access | View all statistics and data |
| Projects | ✅ Full Access | Create, edit, view, delete, mark as done, cancel, view completed |
| Change Orders | ✅ Full Access | Create, edit, view, delete, approve, reject, cancel |
| Purchase Requests | ✅ Full Access | Create, edit, view, delete, approve, submit, cancel |
| Quotations | ✅ Full Access | Create, edit, view, delete, accept, reject, cancel, compare |
| Purchase Orders | ✅ Full Access | Create, edit, view, delete, approve, cancel, print |
| Goods Receipts | ✅ Full Access | Create, edit, view, delete, approve, cancel |
| Goods Returns | ✅ Full Access | Create, edit, view, delete, approve, cancel |
| Inventory | ✅ Full Access | Create, edit, view, delete, adjust stock |
| Material Issuance | ✅ Full Access | Create, edit, view, delete, approve, issue, cancel |
| Reports | ✅ Full Access | View all reports (inventory movement, purchase history, project consumption, supplier performance) |
| Suppliers | ✅ Full Access | Create, edit, view, delete, manage prices |
| Users | ✅ Full Access | Create, edit, view, delete, cancel |

---

### 2. Inventory Manager
**Description:** Manages inventory, goods receipts, goods returns, and material issuance

| Module | Access Level | Permissions |
|--------|-------------|-------------|
| Dashboard | ✅ Full Access | View inventory-related statistics |
| Projects | 📖 Read Only | View projects (for material issuance context) |
| Change Orders | 📖 Read Only | View change orders |
| Purchase Requests | 📖 Read Only | View purchase requests |
| Quotations | 📖 Read Only | View quotations |
| Purchase Orders | 📖 Read Only | View purchase orders |
| Goods Receipts | ✅ Full Access | Create, edit, view, delete, approve, cancel |
| Goods Returns | ✅ Full Access | Create, edit, view, delete, approve, cancel |
| Inventory | ✅ Full Access | Create, edit, view, delete, adjust stock |
| Material Issuance | ✅ Full Access | Create, edit, view, delete, approve, issue, cancel |
| Reports | ⚠️ Limited | View inventory movement and project consumption reports only |
| Suppliers | 📖 Read Only | View suppliers and prices |
| Users | ❌ No Access | Cannot manage users |

---

### 3. Purchasing
**Description:** Handles procurement - Purchase requests, quotations, purchase orders, and suppliers

| Module | Access Level | Permissions |
|--------|-------------|-------------|
| Dashboard | ✅ Full Access | View procurement-related statistics |
| Projects | 📖 Read Only | View projects (for purchase request context) |
| Change Orders | 📖 Read Only | View change orders |
| Purchase Requests | ✅ Full Access | Create, edit, view, delete, submit, cancel (cannot approve) |
| Quotations | ✅ Full Access | Create, edit, view, delete, accept, reject, cancel, compare |
| Purchase Orders | ✅ Full Access | Create, edit, view, delete, cancel, print (cannot approve) |
| Goods Receipts | 📖 Read Only | View goods receipts |
| Goods Returns | 📖 Read Only | View goods returns |
| Inventory | 📖 Read Only | View inventory items |
| Material Issuance | 📖 Read Only | View material issuances |
| Reports | ⚠️ Limited | View purchase history and supplier performance reports only |
| Suppliers | ✅ Full Access | Create, edit, view, delete, manage prices |
| Users | ❌ No Access | Cannot manage users |

---

### 4. Project Manager
**Description:** Manages projects and change orders

| Module | Access Level | Permissions |
|--------|-------------|-------------|
| Dashboard | ✅ Full Access | View project-related statistics |
| Projects | ✅ Full Access | Create, edit, view, delete, mark as done, cancel, view completed (own projects) |
| Change Orders | ✅ Full Access | Create, edit, view, delete, cancel (for own projects) |
| Purchase Requests | ✅ Full Access | Create, edit, view, delete, submit, cancel (for own projects) |
| Quotations | 📖 Read Only | View quotations related to own projects |
| Purchase Orders | 📖 Read Only | View purchase orders related to own projects |
| Goods Receipts | 📖 Read Only | View goods receipts |
| Goods Returns | 📖 Read Only | View goods returns |
| Inventory | 📖 Read Only | View inventory items |
| Material Issuance | 📖 Read Only | View material issuances for own projects |
| Reports | ⚠️ Limited | View project consumption report for own projects only |
| Suppliers | 📖 Read Only | View suppliers |
| Users | ❌ No Access | Cannot manage users |

---

### 5. Warehouse Manager
**Description:** Manages warehouse operations, inspects goods before approval and flags defective items

| Module | Access Level | Permissions |
|--------|-------------|-------------|
| Dashboard | 📖 Read Only | View warehouse-related statistics |
| Projects | 📖 Read Only | View projects |
| Change Orders | 📖 Read Only | View change orders |
| Purchase Requests | 📖 Read Only | View purchase requests |
| Quotations | 📖 Read Only | View quotations |
| Purchase Orders | 📖 Read Only | View purchase orders |
| Goods Receipts | ⚠️ Limited | View, approve/reject (quality inspection), cancel |
| Goods Returns | ✅ Full Access | Create, edit, view, delete, approve, cancel |
| Inventory | 📖 Read Only | View inventory items |
| Material Issuance | 📖 Read Only | View material issuances |
| Reports | ⚠️ Limited | View inventory movement report (for quality tracking) |
| Suppliers | 📖 Read Only | View suppliers |
| Users | ❌ No Access | Cannot manage users |

---

## Summary by Role

### Admin
- **Access:** All modules with full permissions
- **Key Responsibilities:** System administration, user management, approvals across all modules

### Inventory Manager
- **Access:** Inventory, Goods Receipts, Goods Returns, Material Issuance (full access)
- **Key Responsibilities:** Inventory management, receiving goods, processing returns, issuing materials

### Purchasing
- **Access:** Purchase Requests, Quotations, Purchase Orders, Suppliers (full access except approvals)
- **Key Responsibilities:** Procurement workflow, supplier management, quotation comparison

### Project Manager
- **Access:** Projects, Change Orders, Purchase Requests (full access for own projects)
- **Key Responsibilities:** Project management, change order creation, purchase request initiation

### Warehouse Manager
- **Access:** Goods Receipts (approve/reject for quality), Goods Returns (full access)
- **Key Responsibilities:** Warehouse operations, quality inspection of received goods, processing defective item returns

---

## Notes

1. **Approval Workflows:**
   - Purchase Requests: Require approval (typically by Admin or designated approver)
   - Purchase Orders: Require approval (typically by Admin)
   - Goods Receipts: Can be approved by Inventory Manager or Warehouse Manager
   - Change Orders: May require approval depending on project settings

2. **Project Ownership:**
   - Project Managers have full access to their assigned projects
   - Other roles can view projects but cannot modify unless they have Admin access

3. **Cancellation:**
   - All roles with edit access can cancel records (with reason)
   - Cancellation requires a reason (10-1000 characters)

4. **Price Visibility:**
   - System can hide prices based on configuration (`APP_SHOW_PRICES` in `.env`)
   - When prices are hidden, all price-related fields are hidden from UI

5. **Reports:**
   - Each role has access to reports relevant to their responsibilities
   - Admin has access to all reports

