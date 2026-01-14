# Data Dictionary
## Construction & Fabrication ERP System

**Last Updated:** December 2024  
**System:** Inventory & Stock Management System  
**Database:** PostgreSQL

---

## Table of Contents

1. [User Management](#user-management)
2. [Project Management](#project-management)
3. [Supplier Management](#supplier-management)
4. [Inventory Management](#inventory-management)
5. [Procurement Workflow](#procurement-workflow)
6. [Goods Receipt & Return](#goods-receipt--return)
7. [Material Issuance](#material-issuance)
8. [Stock Movements](#stock-movements)
9. [Audit & Logging](#audit--logging)
10. [System Tables](#system-tables)

---

## User Management

### `users`
Stores system user accounts and authentication information.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique user identifier |
| name | varchar(255) | NOT NULL | User's full name |
| email | varchar(255) | UNIQUE, NOT NULL | User's email address (used for login) |
| email_verified_at | timestamp | NULLABLE | Email verification timestamp |
| password | varchar(255) | NOT NULL | Hashed password |
| role_id | bigint | FK → roles.id, NULLABLE | User's role assignment |
| remember_token | varchar(100) | NULLABLE | Remember me token |
| cancellation_reason | text | NULLABLE | Reason for user deactivation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `email`
- Foreign Key: `role_id` → `roles.id`

**Relationships:**
- belongsTo: `Role` (role_id)
- hasMany: `PurchaseRequest` (requested_by, approved_by)
- hasMany: `PurchaseOrder` (created_by, approved_by)
- hasMany: `GoodsReceipt` (received_by, approved_by)
- hasMany: `MaterialIssuance` (requested_by, approved_by, issued_by)

---

### `roles`
Stores user roles and permissions.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique role identifier |
| name | varchar(255) | UNIQUE, NOT NULL | Role name (e.g., "Admin", "Project Manager") |
| slug | varchar(255) | UNIQUE, NOT NULL | URL-friendly role identifier |
| description | text | NULLABLE | Role description |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `name`, `slug`

**Relationships:**
- hasMany: `User` (role_id)

---

## Project Management

### `projects`
Stores project information and tracking.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique project identifier |
| project_code | varchar(255) | UNIQUE, NOT NULL | Unique project code |
| name | varchar(255) | NOT NULL | Project name |
| description | text | NULLABLE | Project description |
| client_id | bigint | FK → users.id, NULLABLE | Client user ID |
| project_manager_id | bigint | FK → users.id, NULLABLE | Project manager user ID |
| start_date | date | NOT NULL | Project start date |
| end_date | date | NOT NULL | Project planned end date |
| actual_end_date | date | NULLABLE | Actual project completion date |
| status | enum | NOT NULL, DEFAULT 'planning' | Project status: planning, active, on_hold, completed, cancelled |
| budget | decimal(15,2) | DEFAULT 0 | Project budget |
| actual_cost | decimal(15,2) | DEFAULT 0 | Actual project cost |
| progress_percentage | integer | DEFAULT 0 | Project completion percentage (0-100) |
| notes | text | NULLABLE | Project notes |
| cancellation_reason | text | NULLABLE | Reason for project cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `project_code`
- Index: `status`, `start_date`
- Foreign Keys: `client_id` → `users.id`, `project_manager_id` → `users.id`

**Relationships:**
- belongsTo: `User` (client_id, project_manager_id)
- hasMany: `PurchaseRequest` (project_id)
- hasMany: `MaterialIssuance` (project_id)
- hasMany: `ChangeOrder` (project_id)

---

### `change_orders`
Stores project change orders.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique change order identifier |
| change_order_number | varchar(255) | UNIQUE, NOT NULL | Unique change order number |
| project_id | bigint | FK → projects.id, NOT NULL | Associated project |
| change_type | enum | NOT NULL | Type of change: scope, budget, schedule, other |
| description | text | NOT NULL | Change order description |
| status | enum | NOT NULL, DEFAULT 'pending' | Status: pending, approved, rejected, cancelled |
| estimated_cost | decimal(15,2) | DEFAULT 0 | Estimated cost impact |
| approved_by | bigint | FK → users.id, NULLABLE | User who approved the change |
| approved_at | timestamp | NULLABLE | Approval timestamp |
| cancellation_reason | text | NULLABLE | Reason for cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `change_order_number`
- Foreign Key: `project_id` → `projects.id`

**Relationships:**
- belongsTo: `Project` (project_id)
- belongsTo: `User` (approved_by)

---

## Supplier Management

### `suppliers`
Stores supplier/vendor information.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique supplier identifier |
| code | varchar(255) | UNIQUE, NOT NULL | Unique supplier code |
| name | varchar(255) | NOT NULL | Supplier name |
| contact_person | varchar(255) | NULLABLE | Primary contact person |
| email | varchar(255) | NULLABLE | Supplier email address |
| phone | varchar(255) | NULLABLE | Supplier phone number |
| address | text | NULLABLE | Supplier address |
| tax_id | varchar(255) | NULLABLE | Tax identification number |
| status | enum | NOT NULL, DEFAULT 'active' | Status: active, inactive |
| notes | text | NULLABLE | Additional notes |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `code`
- Index: `status`

**Relationships:**
- hasMany: `Quotation` (supplier_id)
- hasMany: `PurchaseOrder` (supplier_id)
- hasMany: `SupplierPrice` (supplier_id)

---

### `supplier_prices`
Stores supplier-specific pricing for inventory items.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique price identifier |
| supplier_id | bigint | FK → suppliers.id, NOT NULL | Supplier ID |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item ID |
| unit_price | decimal(15,2) | NOT NULL | Price per unit |
| effective_date | date | NULLABLE | Date when price becomes effective |
| expiry_date | date | NULLABLE | Date when price expires |
| notes | text | NULLABLE | Additional notes |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `(supplier_id, inventory_item_id)`
- Index: `supplier_id`, `inventory_item_id`
- Foreign Keys: `supplier_id` → `suppliers.id`, `inventory_item_id` → `inventory_items.id`

**Relationships:**
- belongsTo: `Supplier` (supplier_id)
- belongsTo: `InventoryItem` (inventory_item_id)

---

## Inventory Management

### `inventory_items`
Stores inventory item master data.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique item identifier |
| item_code | varchar(255) | UNIQUE, NOT NULL | Unique item code |
| name | varchar(255) | NOT NULL | Item name |
| description | text | NULLABLE | Item description |
| category | varchar(255) | NULLABLE | Item category |
| unit_of_measure | varchar(255) | DEFAULT 'pcs' | Unit of measurement (pcs, kg, m, etc.) |
| unit_cost | decimal(10,2) | DEFAULT 0 | Standard unit cost |
| reorder_level | decimal(10,2) | DEFAULT 0 | Minimum stock level before reorder |
| reorder_quantity | decimal(10,2) | DEFAULT 0 | Recommended reorder quantity |
| item_type | enum | NOT NULL, DEFAULT 'raw_material' | Type: raw_material, finished_good, consumable, tool |
| status | enum | NOT NULL, DEFAULT 'active' | Status: active, inactive |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `item_code`
- Index: `item_type`, `status`

**Relationships:**
- hasMany: `PurchaseRequestItem` (inventory_item_id)
- hasMany: `QuotationItem` (inventory_item_id)
- hasMany: `PurchaseOrderItem` (inventory_item_id)
- hasMany: `GoodsReceiptItem` (inventory_item_id)
- hasMany: `MaterialIssuanceItem` (inventory_item_id)
- hasMany: `StockMovement` (inventory_item_id)
- hasMany: `SupplierPrice` (inventory_item_id)

**Note:** Current stock is calculated from `stock_movements` table, not stored directly.

---

## Procurement Workflow

### `purchase_requests`
Stores purchase requests (material requisitions).

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique purchase request identifier |
| pr_number | varchar(255) | UNIQUE, NOT NULL | Unique purchase request number |
| project_id | bigint | FK → projects.id, NULLABLE | Associated project |
| purpose | text | NULLABLE | Purpose of the purchase request |
| status | enum | NOT NULL, DEFAULT 'draft' | Status: draft, submitted, approved, rejected, converted_to_po |
| requested_by | bigint | FK → users.id, NULLABLE | User who created the request |
| approved_by | bigint | FK → users.id, NULLABLE | User who approved the request |
| approved_at | timestamp | NULLABLE | Approval timestamp |
| notes | text | NULLABLE | Additional notes |
| cancellation_reason | text | NULLABLE | Reason for cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `pr_number`
- Index: `status`
- Foreign Keys: `project_id` → `projects.id`, `requested_by` → `users.id`, `approved_by` → `users.id`

**Relationships:**
- belongsTo: `Project` (project_id)
- belongsTo: `User` (requested_by, approved_by)
- hasMany: `PurchaseRequestItem` (purchase_request_id)
- hasMany: `Quotation` (purchase_request_id)
- hasMany: `PurchaseOrder` (purchase_request_id)

---

### `purchase_request_items`
Stores items in a purchase request.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique item identifier |
| purchase_request_id | bigint | FK → purchase_requests.id, NOT NULL | Parent purchase request |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item |
| quantity | decimal(10,2) | NOT NULL | Requested quantity |
| unit_cost | decimal(10,2) | DEFAULT 0 | Estimated unit cost |
| specifications | text | NULLABLE | Item specifications |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Foreign Keys: `purchase_request_id` → `purchase_requests.id`, `inventory_item_id` → `inventory_items.id`

**Relationships:**
- belongsTo: `PurchaseRequest` (purchase_request_id)
- belongsTo: `InventoryItem` (inventory_item_id)

---

### `quotations`
Stores supplier quotations for purchase requests.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique quotation identifier |
| quotation_number | varchar(255) | UNIQUE, NOT NULL | Unique quotation number |
| purchase_request_id | bigint | FK → purchase_requests.id, NOT NULL | Associated purchase request |
| supplier_id | bigint | FK → suppliers.id, NULLABLE | Supplier (can be null if items have different suppliers) |
| quotation_date | date | NOT NULL | Quotation date |
| valid_until | date | NOT NULL | Quotation expiration date |
| status | enum | NOT NULL, DEFAULT 'pending' | Status: pending, accepted, rejected, expired |
| total_amount | decimal(15,2) | DEFAULT 0 | Total quotation amount |
| project_code | varchar(255) | NULLABLE | Project code reference |
| terms_conditions | text | NULLABLE | Terms and conditions |
| notes | text | NULLABLE | Additional notes |
| cancellation_reason | text | NULLABLE | Reason for cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `quotation_number`
- Index: `status`
- Foreign Keys: `purchase_request_id` → `purchase_requests.id`, `supplier_id` → `suppliers.id`

**Relationships:**
- belongsTo: `PurchaseRequest` (purchase_request_id)
- belongsTo: `Supplier` (supplier_id) - nullable
- hasMany: `QuotationItem` (quotation_id)
- hasMany: `PurchaseOrder` (quotation_id)

---

### `quotation_items`
Stores items in a quotation.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique item identifier |
| quotation_id | bigint | FK → quotations.id, NOT NULL | Parent quotation |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item |
| supplier_id | bigint | FK → suppliers.id, NULLABLE | Supplier for this item (if different from main supplier) |
| quantity | decimal(10,2) | NOT NULL | Quoted quantity |
| unit_price | decimal(10,2) | NULLABLE | Unit price |
| total_price | decimal(10,2) | NULLABLE | Total price (quantity × unit_price) |
| specifications | text | NULLABLE | Item specifications |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Foreign Keys: `quotation_id` → `quotations.id`, `inventory_item_id` → `inventory_items.id`, `supplier_id` → `suppliers.id`

**Relationships:**
- belongsTo: `Quotation` (quotation_id)
- belongsTo: `InventoryItem` (inventory_item_id)
- belongsTo: `Supplier` (supplier_id) - nullable

---

### `purchase_orders`
Stores purchase orders created from quotations.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique purchase order identifier |
| po_number | varchar(255) | UNIQUE, NOT NULL | Unique purchase order number |
| purchase_request_id | bigint | FK → purchase_requests.id, NULLABLE | Associated purchase request |
| quotation_id | bigint | FK → quotations.id, NULLABLE | Associated quotation |
| supplier_id | bigint | FK → suppliers.id, NULLABLE | Main supplier (can be null if items have different suppliers) |
| project_code | varchar(255) | NULLABLE | Project code reference |
| po_date | date | NOT NULL | Purchase order date |
| expected_delivery_date | date | NULLABLE | Expected delivery date |
| status | enum | NOT NULL, DEFAULT 'draft' | Status: draft, pending, approved, sent, partially_received, completed, cancelled |
| subtotal | decimal(15,2) | DEFAULT 0 | Subtotal amount |
| tax_amount | decimal(15,2) | DEFAULT 0 | Tax amount |
| total_amount | decimal(15,2) | DEFAULT 0 | Total amount (subtotal + tax) |
| terms_conditions | text | NULLABLE | Terms and conditions |
| delivery_address | text | NULLABLE | Delivery address |
| created_by | bigint | FK → users.id, NULLABLE | User who created the PO |
| approved_by | bigint | FK → users.id, NULLABLE | User who approved the PO |
| approved_at | timestamp | NULLABLE | Approval timestamp |
| notes | text | NULLABLE | Additional notes |
| cancellation_reason | text | NULLABLE | Reason for cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `po_number`
- Index: `status`
- Foreign Keys: `purchase_request_id` → `purchase_requests.id`, `quotation_id` → `quotations.id`, `supplier_id` → `suppliers.id`, `created_by` → `users.id`, `approved_by` → `users.id`

**Relationships:**
- belongsTo: `PurchaseRequest` (purchase_request_id)
- belongsTo: `Quotation` (quotation_id)
- belongsTo: `Supplier` (supplier_id) - nullable
- belongsTo: `User` (created_by, approved_by)
- hasMany: `PurchaseOrderItem` (purchase_order_id)
- hasMany: `GoodsReceipt` (purchase_order_id)

---

### `purchase_order_items`
Stores items in a purchase order.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique item identifier |
| purchase_order_id | bigint | FK → purchase_orders.id, NOT NULL | Parent purchase order |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item |
| supplier_id | bigint | FK → suppliers.id, NULLABLE | Supplier for this item (if different from main supplier) |
| quantity | decimal(10,2) | NOT NULL | Ordered quantity |
| unit_price | decimal(10,2) | NOT NULL | Unit price |
| total_price | decimal(10,2) | NOT NULL | Total price (quantity × unit_price) |
| received_quantity | decimal(10,2) | DEFAULT 0 | Quantity received so far |
| specifications | text | NULLABLE | Item specifications |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Foreign Keys: `purchase_order_id` → `purchase_orders.id`, `inventory_item_id` → `inventory_items.id`, `supplier_id` → `suppliers.id`

**Relationships:**
- belongsTo: `PurchaseOrder` (purchase_order_id)
- belongsTo: `InventoryItem` (inventory_item_id)
- belongsTo: `Supplier` (supplier_id) - nullable
- hasMany: `GoodsReceiptItem` (purchase_order_item_id)

---

## Goods Receipt & Return

### `goods_receipts`
Stores goods receipt records when items are received from suppliers.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique goods receipt identifier |
| gr_number | varchar(255) | UNIQUE, NOT NULL | Unique goods receipt number |
| purchase_order_id | bigint | FK → purchase_orders.id, NOT NULL | Associated purchase order |
| project_code | varchar(255) | NULLABLE | Project code reference |
| gr_date | date | NOT NULL | Goods receipt date |
| status | enum | NOT NULL, DEFAULT 'draft' | Status: draft, pending, approved, rejected |
| delivery_note_number | text | NULLABLE | Delivery note number from supplier |
| remarks | text | NULLABLE | Receipt remarks |
| received_by | bigint | FK → users.id, NULLABLE | User who received the goods |
| approved_by | bigint | FK → users.id, NULLABLE | User who approved the receipt |
| approved_at | timestamp | NULLABLE | Approval timestamp |
| rejected_at | timestamp | NULLABLE | Rejection timestamp |
| cancellation_reason | text | NULLABLE | Reason for cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `gr_number`
- Index: `status`
- Foreign Keys: `purchase_order_id` → `purchase_orders.id`, `received_by` → `users.id`, `approved_by` → `users.id`

**Relationships:**
- belongsTo: `PurchaseOrder` (purchase_order_id)
- belongsTo: `User` (received_by, approved_by)
- hasMany: `GoodsReceiptItem` (goods_receipt_id)
- hasMany: `GoodsReturn` (goods_receipt_id)

---

### `goods_receipt_items`
Stores items received in a goods receipt.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique item identifier |
| goods_receipt_id | bigint | FK → goods_receipts.id, NOT NULL | Parent goods receipt |
| purchase_order_item_id | bigint | FK → purchase_order_items.id, NOT NULL | Associated purchase order item |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item |
| quantity_ordered | decimal(10,2) | NOT NULL | Quantity ordered in PO |
| quantity_received | decimal(10,2) | NOT NULL | Quantity actually received |
| quantity_accepted | decimal(10,2) | NOT NULL | Quantity accepted into inventory |
| quantity_rejected | decimal(10,2) | DEFAULT 0 | Quantity rejected |
| rejection_reason | text | NULLABLE | Reason for rejection |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Foreign Keys: `goods_receipt_id` → `goods_receipts.id`, `purchase_order_item_id` → `purchase_order_items.id`, `inventory_item_id` → `inventory_items.id`

**Relationships:**
- belongsTo: `GoodsReceipt` (goods_receipt_id)
- belongsTo: `PurchaseOrderItem` (purchase_order_item_id)
- belongsTo: `InventoryItem` (inventory_item_id)
- hasMany: `GoodsReturnItem` (goods_receipt_item_id)

---

### `goods_returns`
Stores goods return records for items returned to suppliers.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique goods return identifier |
| return_number | varchar(255) | UNIQUE, NOT NULL | Unique return number |
| goods_receipt_id | bigint | FK → goods_receipts.id, NOT NULL | Associated goods receipt |
| project_code | varchar(255) | NULLABLE | Project code reference |
| return_date | date | NOT NULL | Return date |
| status | enum | NOT NULL, DEFAULT 'pending' | Status: pending, approved, rejected, cancelled |
| reason | text | NOT NULL | Reason for return |
| returned_by | bigint | FK → users.id, NULLABLE | User who initiated the return |
| approved_by | bigint | FK → users.id, NULLABLE | User who approved the return |
| approved_at | timestamp | NULLABLE | Approval timestamp |
| notes | text | NULLABLE | Additional notes |
| cancellation_reason | text | NULLABLE | Reason for cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `return_number`
- Foreign Keys: `goods_receipt_id` → `goods_receipts.id`, `returned_by` → `users.id`, `approved_by` → `users.id`

**Relationships:**
- belongsTo: `GoodsReceipt` (goods_receipt_id)
- belongsTo: `User` (returned_by, approved_by)
- hasMany: `GoodsReturnItem` (goods_return_id)

---

### `goods_return_items`
Stores items returned in a goods return.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique item identifier |
| goods_return_id | bigint | FK → goods_returns.id, NOT NULL | Parent goods return |
| goods_receipt_item_id | bigint | FK → goods_receipt_items.id, NOT NULL | Associated goods receipt item |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item |
| quantity | decimal(10,2) | NOT NULL | Quantity returned |
| reason | text | NULLABLE | Item-specific return reason |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Foreign Keys: `goods_return_id` → `goods_returns.id`, `goods_receipt_item_id` → `goods_receipt_items.id`, `inventory_item_id` → `inventory_items.id`

**Relationships:**
- belongsTo: `GoodsReturn` (goods_return_id)
- belongsTo: `GoodsReceiptItem` (goods_receipt_item_id)
- belongsTo: `InventoryItem` (inventory_item_id)

---

## Material Issuance

### `material_issuances`
Stores material issuance records for issuing items to projects or work orders.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique material issuance identifier |
| issuance_number | varchar(255) | UNIQUE, NOT NULL | Unique issuance number |
| project_id | bigint | FK → projects.id, NULLABLE | Associated project |
| issuance_type | enum | NOT NULL | Type: project, maintenance, general, repair, other |
| work_order_number | varchar(255) | NULLABLE | Work order number |
| issuance_date | date | NOT NULL | Issuance date |
| status | enum | NOT NULL, DEFAULT 'draft' | Status: draft, approved, issued, completed |
| purpose | text | NULLABLE | Purpose of issuance |
| requested_by | bigint | FK → users.id, NULLABLE | User who requested the issuance |
| approved_by | bigint | FK → users.id, NULLABLE | User who approved the issuance |
| issued_by | bigint | FK → users.id, NULLABLE | User who issued the materials |
| approved_at | timestamp | NULLABLE | Approval timestamp |
| issued_at | timestamp | NULLABLE | Issuance timestamp |
| notes | text | NULLABLE | Additional notes |
| cancellation_reason | text | NULLABLE | Reason for cancellation |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |
| deleted_at | timestamp | NULLABLE | Soft delete timestamp |

**Indexes:**
- Primary Key: `id`
- Unique: `issuance_number`
- Index: `status`
- Foreign Keys: `project_id` → `projects.id`, `requested_by` → `users.id`, `approved_by` → `users.id`, `issued_by` → `users.id`

**Relationships:**
- belongsTo: `Project` (project_id)
- belongsTo: `User` (requested_by, approved_by, issued_by)
- hasMany: `MaterialIssuanceItem` (material_issuance_id)

---

### `material_issuance_items`
Stores items issued in a material issuance.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique item identifier |
| material_issuance_id | bigint | FK → material_issuances.id, NOT NULL | Parent material issuance |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item |
| quantity | decimal(10,2) | NOT NULL | Quantity issued |
| unit_cost | decimal(10,2) | DEFAULT 0 | Unit cost at time of issuance |
| notes | text | NULLABLE | Item-specific notes |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Foreign Keys: `material_issuance_id` → `material_issuances.id`, `inventory_item_id` → `inventory_items.id`

**Relationships:**
- belongsTo: `MaterialIssuance` (material_issuance_id)
- belongsTo: `InventoryItem` (inventory_item_id)

---

## Stock Movements

### `stock_movements`
Stores all inventory stock movements (in/out/adjustments).

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique movement identifier |
| inventory_item_id | bigint | FK → inventory_items.id, NOT NULL | Inventory item |
| movement_type | enum | NOT NULL | Type: stock_in, stock_out, adjustment_in, adjustment_out, return_in, return_out |
| reference_type | varchar(255) | NULLABLE | Polymorphic reference type (e.g., 'App\Models\GoodsReceipt') |
| reference_id | bigint | NULLABLE | Polymorphic reference ID |
| quantity | decimal(10,2) | NOT NULL | Movement quantity (positive for in, negative for out) |
| unit_cost | decimal(10,2) | DEFAULT 0 | Unit cost at time of movement |
| balance_after | decimal(10,2) | NOT NULL | Stock balance after this movement |
| notes | text | NULLABLE | Movement notes |
| created_by | bigint | FK → users.id, NULLABLE | User who created the movement |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Index: `movement_type`, `(reference_type, reference_id)`, `created_at`
- Foreign Keys: `inventory_item_id` → `inventory_items.id`, `created_by` → `users.id`

**Relationships:**
- belongsTo: `InventoryItem` (inventory_item_id)
- belongsTo: `User` (created_by)
- MorphTo: `reference` (reference_type, reference_id) - can reference GoodsReceipt, GoodsReturn, MaterialIssuance, etc.

**Movement Types:**
- `stock_in`: Items received (from GoodsReceipt)
- `stock_out`: Items issued (from MaterialIssuance)
- `adjustment_in`: Manual stock increase
- `adjustment_out`: Manual stock decrease
- `return_in`: Items returned from project (from GoodsReturn)
- `return_out`: Items returned to supplier

---

## Audit & Logging

### `audit_logs`
Stores audit trail of all system changes.

| Column Name | Data Type | Constraints | Description |
|------------|-----------|-------------|-------------|
| id | bigint | PK, Auto Increment | Unique audit log identifier |
| model_type | varchar(255) | NOT NULL | Model class name (e.g., 'App\Models\PurchaseOrder') |
| model_id | bigint | NOT NULL | Model record ID |
| action | varchar(255) | NOT NULL | Action performed: created, updated, deleted, etc. |
| old_values | text | NULLABLE | JSON of old values (before change) |
| new_values | text | NULLABLE | JSON of new values (after change) |
| description | text | NULLABLE | Human-readable description of the change |
| user_id | bigint | FK → users.id, NULLABLE | User who performed the action |
| ip_address | varchar(255) | NULLABLE | IP address of the user |
| user_agent | text | NULLABLE | User agent string |
| created_at | timestamp | NULLABLE | Record creation timestamp |
| updated_at | timestamp | NULLABLE | Record last update timestamp |

**Indexes:**
- Primary Key: `id`
- Index: `(model_type, model_id)`, `created_at`
- Foreign Key: `user_id` → `users.id`

**Relationships:**
- belongsTo: `User` (user_id)
- MorphTo: `model` (model_type, model_id) - can reference any model

---

## System Tables

### `cache`
Laravel cache table (system table).

### `cache_locks`
Laravel cache locks table (system table).

### `jobs`
Laravel queue jobs table (system table).

### `job_batches`
Laravel job batches table (system table).

### `failed_jobs`
Laravel failed jobs table (system table).

### `sessions`
Laravel session storage table (system table).

### `password_reset_tokens`
Laravel password reset tokens table (system table).

---

## Notes

1. **Soft Deletes**: Most tables use soft deletes (`deleted_at` column) to maintain data integrity while allowing record recovery.

2. **Timestamps**: All tables include `created_at` and `updated_at` timestamps for audit purposes.

3. **Foreign Key Constraints**: Foreign keys use `onDelete('cascade')` for child records and `onDelete('set null')` for optional parent references.

4. **Polymorphic Relationships**: `stock_movements` uses polymorphic relationships to reference different source models.

5. **Status Enums**: Most workflow tables use enum status fields to track state transitions.

6. **Decimal Precision**: Financial amounts use `decimal(15,2)` for high precision, while quantities use `decimal(10,2)`.

7. **Project Code**: Several tables include `project_code` as a denormalized field for easier querying and reporting.

---

**Document Version:** 1.0  
**Last Updated:** December 2024



