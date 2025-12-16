# Entity Relationship Diagram (ERD)
## Construction & Fabrication ERP System

**Last Updated:** December 2024  
**System:** Inventory & Stock Management System

---

## **Overview**

This document provides a comprehensive Entity Relationship Diagram for the Construction & Fabrication ERP System, focusing on inventory management, procurement workflows, and project tracking. The system manages the complete lifecycle from purchase requests to stock movements.

---

## **1. USER MANAGEMENT & ACCESS CONTROL**

### **Core Entities:**

```
┌─────────────┐         ┌─────────────┐
│    Role     │◄────────│    User     │
│─────────────│         │─────────────│
│ id (PK)     │         │ id (PK)     │
│ name        │         │ name        │
│ slug        │         │ email       │
│ description │         │ password    │
│ timestamps  │         │ role_id (FK)│
│ deleted_at  │         │ cancellation│
└─────────────┘         │   _reason   │
                        │ timestamps  │
                        │ deleted_at  │
                        └─────────────┘
```

**Relationships:**
- `Role` hasMany `User` (1:N)
- `User` belongsTo `Role` (role_id)

**Roles in System:**
- Admin
- Project Manager
- Purchasing
- Inventory Manager
- Warehouse Manager

---

## **2. PROJECT MANAGEMENT**

```
┌─────────────┐         ┌─────────────┐
│   Project   │◄────────│ ChangeOrder │
│─────────────│         │─────────────│
│ id (PK)     │         │ id (PK)     │
│ project_code│         │ project_id  │
│ name        │         │ change_order│
│ description │         │   _number   │
│ project_    │         │ description │
│  manager_id │         │ reason      │
│ start_date  │         │ additional_ │
│ end_date    │         │   days      │
│ actual_end_ │         │ additional_ │
│   date      │         │   cost      │
│ status      │         │ status      │
│ budget      │         │ requested_by│
│ actual_cost │         │ approved_by │
│ progress_   │         │ approved_at │
│  percentage │         │ approval_   │
│ notes       │         │   notes     │
│ cancellation│         │ cancellation│
│   _reason   │         │   _reason   │
│ timestamps  │         │ timestamps  │
│ deleted_at  │         │ deleted_at  │
└─────────────┘         └─────────────┘
      │
      │ 1:N
      ▼
┌─────────────┐
│Purchase     │
│Request      │
│─────────────│
│ id (PK)     │
│ pr_number   │
│ project_id  │
│ purpose     │
│ status      │
│ requested_by│
│ approved_by │
│ approved_at │
│ notes       │
│ timestamps  │
│ deleted_at  │
└─────────────┘
      │
      │ 1:N
      ▼
┌─────────────┐
│Material     │
│Issuance     │
│─────────────│
│ id (PK)     │
│ issuance_   │
│   number    │
│ project_id  │
│ work_order_ │
│   number    │
│ issuance_   │
│   type      │
│ issuance_   │
│   date      │
│ status      │
│ purpose     │
│ requested_by│
│ approved_by │
│ issued_by   │
│ approved_at │
│ issued_at   │
│ notes       │
│ timestamps  │
│ deleted_at  │
└─────────────┘
```

**Relationships:**
- `Project` belongsTo `User` (project_manager_id)
- `Project` hasMany `ChangeOrder`
- `Project` hasMany `PurchaseRequest`
- `Project` hasMany `MaterialIssuance`
- `Project` hasManyThrough `Quotation` (via PurchaseRequest)
- `Project` hasManyThrough `PurchaseOrder` (via PurchaseRequest)
- `ChangeOrder` belongsTo `Project`
- `ChangeOrder` belongsTo `User` (requested_by, approved_by)
- `PurchaseRequest` belongsTo `Project`
- `PurchaseRequest` belongsTo `User` (requested_by, approved_by)
- `MaterialIssuance` belongsTo `Project`
- `MaterialIssuance` belongsTo `User` (requested_by, approved_by, issued_by)

---

## **3. PROCUREMENT WORKFLOW**

### **3.1 Purchase Request Flow**

```
┌─────────────────┐
│PurchaseRequest  │
│─────────────────│
│ id (PK)         │
│ pr_number       │
│ project_id (FK) │
│ purpose         │
│ status          │
│ requested_by    │
│ approved_by     │
│ timestamps      │
└─────────────────┘
      │
      │ 1:N
      ▼
┌─────────────────┐
│PurchaseRequest  │
│     Item        │
│─────────────────│
│ id (PK)         │
│ purchase_       │
│  request_id (FK)│
│ inventory_      │
│  item_id (FK)   │
│ quantity        │
│ unit_cost       │
│ specifications  │
└─────────────────┘
```

### **3.2 Quotation Flow**

```
┌─────────────┐         ┌─────────────┐
│Purchase     │◄────────│ Quotation   │
│Request      │         │─────────────│
└─────────────┘         │ id (PK)     │
                        │ quotation_  │
                        │   number    │
                        │ project_code│
                        │ purchase_   │
                        │  request_id │
                        │ supplier_id │
                        │ quotation_  │
                        │   date      │
                        │ valid_until │
                        │ status      │
                        │ total_amount│
                        │ terms_      │
                        │  conditions │
                        │ notes       │
                        │ timestamps  │
                        │ deleted_at  │
                        └─────────────┘
                              │
                              │ 1:N
                              ▼
                        ┌─────────────┐
                        │Quotation    │
                        │   Item      │
                        │─────────────│
                        │ id (PK)     │
                        │ quotation_  │
                        │  id (FK)    │
                        │ supplier_id │
                        │ inventory_  │
                        │  item_id    │
                        │ quantity    │
                        │ unit_price  │
                        │ total_price │
                        │ specs       │
                        └─────────────┘
```

### **3.3 Purchase Order Flow**

```
┌─────────────┐         ┌─────────────┐
│ Quotation   │◄────────│PurchaseOrder│
│             │         │─────────────│
└─────────────┘         │ id (PK)     │
                        │ po_number   │
                        │ project_code│
                        │ purchase_   │
                        │  request_id │
                        │ quotation_id│
                        │ supplier_id │
                        │ po_date     │
                        │ expected_   │
                        │  delivery_  │
                        │  date       │
                        │ status      │
                        │ subtotal    │
                        │ tax_amount  │
                        │ total_amount│
                        │ terms_      │
                        │  conditions │
                        │ delivery_   │
                        │  address    │
                        │ created_by  │
                        │ approved_by │
                        │ approved_at │
                        │ notes       │
                        │ timestamps  │
                        │ deleted_at  │
                        └─────────────┘
                              │
                              │ 1:N
                              ▼
                        ┌─────────────┐
                        │PurchaseOrder│
                        │   Item      │
                        │─────────────│
                        │ id (PK)     │
                        │ purchase_   │
                        │  order_id   │
                        │ supplier_id │
                        │ inventory_  │
                        │  item_id    │
                        │ quantity    │
                        │ unit_price  │
                        │ total_price │
                        │ received_   │
                        │  quantity   │
                        │ specs       │
                        └─────────────┘
```

**Relationships:**
- `PurchaseRequest` belongsTo `Project`
- `PurchaseRequest` hasMany `PurchaseRequestItem`
- `PurchaseRequest` hasMany `Quotation`
- `PurchaseRequest` hasMany `PurchaseOrder`
- `PurchaseRequestItem` belongsTo `PurchaseRequest`
- `PurchaseRequestItem` belongsTo `InventoryItem`
- `Quotation` belongsTo `PurchaseRequest`
- `Quotation` belongsTo `Supplier`
- `Quotation` hasMany `QuotationItem`
- `Quotation` hasMany `PurchaseOrder`
- `QuotationItem` belongsTo `Quotation`
- `QuotationItem` belongsTo `Supplier`
- `QuotationItem` belongsTo `InventoryItem`
- `PurchaseOrder` belongsTo `PurchaseRequest`
- `PurchaseOrder` belongsTo `Quotation`
- `PurchaseOrder` belongsTo `Supplier`
- `PurchaseOrder` belongsTo `User` (created_by, approved_by)
- `PurchaseOrder` hasMany `PurchaseOrderItem`
- `PurchaseOrder` hasMany `GoodsReceipt`
- `PurchaseOrderItem` belongsTo `PurchaseOrder`
- `PurchaseOrderItem` belongsTo `Supplier`
- `PurchaseOrderItem` belongsTo `InventoryItem`
- `PurchaseOrderItem` hasMany `GoodsReceiptItem`

---

## **4. INVENTORY MANAGEMENT**

### **4.1 Core Inventory Entity**

```
┌─────────────────┐
│ InventoryItem   │
│─────────────────│
│ id (PK)         │
│ item_code       │
│ name            │
│ description     │
│ category        │
│ unit_of_measure │
│ unit_cost       │
│ reorder_level   │
│ reorder_quantity│
│ item_type       │
│ status          │
│ timestamps      │
│ deleted_at      │
└─────────────────┘
      │
      │ 1:N (to all item tables)
      │
      ├─────────┬──────────┬──────────────┬───────────────┐
      ▼         ▼          ▼              ▼               ▼
┌──────────┐┌─────────┐┌──────────┐┌─────────────┐┌──────────────┐
│Purchase  ││Quotation││Purchase  ││GoodsReceipt ││Material      │
│Request   ││Item     ││OrderItem ││Item         ││IssuanceItem  │
│Item      ││         ││          ││             ││              │
└──────────┘└─────────┘└──────────┘└─────────────┘└──────────────┘
```

### **4.2 Stock Movement Tracking**

```
┌─────────────────┐         ┌─────────────────┐
│ InventoryItem   │◄────────│ StockMovement   │
│                 │         │─────────────────│
└─────────────────┘         │ id (PK)         │
                            │ inventory_      │
                            │  item_id (FK)   │
                            │ movement_type   │
                            │ reference_type  │
                            │ reference_id    │
                            │ quantity        │
                            │ unit_cost       │
                            │ balance_after   │
                            │ notes           │
                            │ created_by      │
                            │ timestamps      │
                            └─────────────────┘
```

**Movement Types:**
- stock_in
- stock_out
- adjustment_in
- adjustment_out
- return_in
- return_out

**Reference Types (Polymorphic):**
- GoodsReceipt
- GoodsReturn
- MaterialIssuance
- Manual adjustments

**Relationships:**
- `InventoryItem` hasMany `StockMovement`
- `InventoryItem` hasMany `PurchaseRequestItem`
- `InventoryItem` hasMany `QuotationItem`
- `InventoryItem` hasMany `PurchaseOrderItem`
- `InventoryItem` hasMany `GoodsReceiptItem`
- `InventoryItem` hasMany `MaterialIssuanceItem`
- `InventoryItem` hasMany `SupplierPrice`
- `StockMovement` belongsTo `InventoryItem`
- `StockMovement` belongsTo `User` (created_by)
- `StockMovement` morphTo `reference` (polymorphic)

---

## **5. GOODS RECEIPT & RETURN MANAGEMENT**

### **5.1 Goods Receipt Flow**

```
┌─────────────┐         ┌─────────────┐
│PurchaseOrder│◄────────│GoodsReceipt │
│             │         │─────────────│
└─────────────┘         │ id (PK)     │
                        │ gr_number   │
                        │ project_code│
                        │ purchase_   │
                        │  order_id   │
                        │ gr_date     │
                        │ status      │
                        │ delivery_   │
                        │  note_number│
                        │ remarks     │
                        │ received_by │
                        │ approved_by │
                        │ approved_at │
                        │ rejected_at │
                        │ timestamps  │
                        │ deleted_at  │
                        └─────────────┘
                              │
                              │ 1:N
                              ▼
                        ┌─────────────┐
                        │GoodsReceipt │
                        │   Item      │
                        │─────────────│
                        │ id (PK)     │
                        │ goods_      │
                        │  receipt_id │
                        │ purchase_   │
                        │  order_item │
                        │   _id (FK)  │
                        │ inventory_  │
                        │  item_id    │
                        │ quantity_   │
                        │  ordered    │
                        │ quantity_   │
                        │  received   │
                        │ quantity_   │
                        │  accepted   │
                        │ quantity_   │
                        │  rejected   │
                        │ rejection_  │
                        │  reason     │
                        └─────────────┘
```

### **5.2 Goods Return Flow**

```
┌─────────────┐         ┌─────────────┐
│GoodsReceipt │◄────────│GoodsReturn  │
│             │         │─────────────│
└─────────────┘         │ id (PK)     │
                        │ return_     │
                        │   number    │
                        │ project_code│
                        │ goods_      │
                        │  receipt_id │
                        │ return_date │
                        │ status      │
                        │ reason      │
                        │ returned_by │
                        │ approved_by │
                        │ approved_at │
                        │ notes       │
                        │ timestamps  │
                        │ deleted_at  │
                        └─────────────┘
                              │
                              │ 1:N
                              ▼
                        ┌─────────────┐
                        │GoodsReturn  │
                        │   Item      │
                        │─────────────│
                        │ id (PK)     │
                        │ goods_      │
                        │  return_id  │
                        │ goods_      │
                        │  receipt_   │
                        │  item_id    │
                        │ inventory_  │
                        │  item_id    │
                        │ quantity    │
                        │ reason      │
                        └─────────────┘
```

**Relationships:**
- `GoodsReceipt` belongsTo `PurchaseOrder`
- `GoodsReceipt` belongsTo `User` (received_by, approved_by)
- `GoodsReceipt` hasMany `GoodsReceiptItem`
- `GoodsReceipt` hasMany `GoodsReturn`
- `GoodsReceiptItem` belongsTo `GoodsReceipt`
- `GoodsReceiptItem` belongsTo `PurchaseOrderItem`
- `GoodsReceiptItem` belongsTo `InventoryItem`
- `GoodsReceiptItem` hasMany `GoodsReturnItem`
- `GoodsReturn` belongsTo `GoodsReceipt`
- `GoodsReturn` belongsTo `User` (returned_by, approved_by)
- `GoodsReturn` hasMany `GoodsReturnItem`
- `GoodsReturnItem` belongsTo `GoodsReturn`
- `GoodsReturnItem` belongsTo `GoodsReceiptItem`
- `GoodsReturnItem` belongsTo `InventoryItem`

---

## **6. MATERIAL ISSUANCE**

```
┌─────────────┐         ┌─────────────┐
│   Project   │◄────────│Material     │
│             │         │Issuance     │
└─────────────┘         └─────────────┘
                              │
                              │ 1:N
                              ▼
                        ┌─────────────┐
                        │Material     │
                        │Issuance     │
                        │   Item      │
                        │─────────────│
                        │ id (PK)     │
                        │ material_   │
                        │  issuance_id│
                        │ inventory_  │
                        │  item_id    │
                        │ quantity    │
                        │ unit_cost   │
                        │ notes       │
                        └─────────────┘
```

**Relationships:**
- `MaterialIssuance` belongsTo `Project`
- `MaterialIssuance` belongsTo `User` (requested_by, approved_by, issued_by)
- `MaterialIssuance` hasMany `MaterialIssuanceItem`
- `MaterialIssuanceItem` belongsTo `MaterialIssuance`
- `MaterialIssuanceItem` belongsTo `InventoryItem`

---

## **7. SUPPLIER MANAGEMENT**

```
┌─────────────┐         ┌─────────────┐
│  Supplier   │◄────────│SupplierPrice│
│─────────────│         │─────────────│
│ id (PK)     │         │ id (PK)     │
│ code        │         │ supplier_id │
│ name        │         │ inventory_  │
│ contact_    │         │  item_id    │
│  person     │         │ unit_price  │
│ email       │         │ effective_  │
│ phone       │         │  date       │
│ address     │         │ expiry_date │
│ tax_id      │         │ notes       │
│ status      │         │ timestamps  │
│ notes       │         └─────────────┘
│ timestamps  │
│ deleted_at  │
└─────────────┘
      │
      │ 1:N
      ├──────────────┬──────────────┐
      ▼              ▼              ▼
┌──────────┐  ┌──────────┐  ┌──────────┐
│Quotation │  │Purchase  │  │Purchase  │
│          │  │Order     │  │OrderItem │
│          │  │          │  │          │
└──────────┘  └──────────┘  └──────────┘
```

**Relationships:**
- `Supplier` hasMany `Quotation`
- `Supplier` hasMany `PurchaseOrder`
- `Supplier` hasMany `SupplierPrice`
- `SupplierPrice` belongsTo `Supplier`
- `SupplierPrice` belongsTo `InventoryItem`
- `QuotationItem` belongsTo `Supplier`
- `PurchaseOrderItem` belongsTo `Supplier`

---

## **8. AUDIT LOGGING**

```
┌─────────────┐         ┌─────────────┐
│    User     │◄────────│ AuditLog    │
│             │         │─────────────│
└─────────────┘         │ id (PK)     │
                        │ model_type  │
                        │ model_id    │
                        │ action      │
                        │ description │
                        │ old_values  │
                        │ new_values  │
                        │ user_id (FK)│
                        │ ip_address  │
                        │ user_agent  │
                        │ timestamps  │
                        └─────────────┘
```

**Relationships:**
- `AuditLog` belongsTo `User`
- `AuditLog` morphTo `model` (polymorphic - tracks changes to any model)

**Audited Actions:**
- created
- updated
- deleted
- restored
- approved
- rejected
- cancelled
- stock_adjusted
- login
- logout
- login_failed

---

## **9. COMPLETE RELATIONSHIP OVERVIEW**

### **Central Entity: InventoryItem**

The `InventoryItem` entity is central to the system, connecting to:
- PurchaseRequestItem
- QuotationItem
- PurchaseOrderItem
- GoodsReceiptItem
- GoodsReturnItem
- MaterialIssuanceItem
- SupplierPrice
- StockMovement

### **Procurement Workflow Chain:**

```
Project
  └─► PurchaseRequest
        ├─► PurchaseRequestItem → InventoryItem
        ├─► Quotation
        │     └─► QuotationItem → InventoryItem
        └─► PurchaseOrder
              └─► PurchaseOrderItem → InventoryItem
                    └─► GoodsReceipt
                          ├─► GoodsReceiptItem → InventoryItem
                          │                        └─► StockMovement
                          └─► GoodsReturn
                                └─► GoodsReturnItem → InventoryItem
                                                      └─► StockMovement
```

### **Material Issuance Flow:**

```
Project
  └─► MaterialIssuance
        └─► MaterialIssuanceItem → InventoryItem
                                      └─► StockMovement
```

### **User Involvement:**

Users (via roles) are involved in:
- Project Management (project_manager_id)
- Purchase Requests (requested_by, approved_by)
- Purchase Orders (created_by, approved_by)
- Change Orders (requested_by, approved_by)
- Material Issuances (requested_by, approved_by, issued_by)
- Goods Receipts (received_by, approved_by)
- Goods Returns (returned_by, approved_by)
- Stock Movements (created_by)
- Audit Logs (user_id)

---

## **10. KEY DESIGN PATTERNS**

### **10.1 Soft Deletes**
The following entities use soft deletes:
- Users
- Roles
- Projects
- ChangeOrders
- PurchaseRequests
- Quotations
- PurchaseOrders
- GoodsReceipts
- GoodsReturns
- InventoryItems
- Suppliers
- MaterialIssuances

### **10.2 Polymorphic Relationships**
- `StockMovement.reference` → polymorphic (GoodsReceipt, GoodsReturn, MaterialIssuance, etc.)
- `AuditLog.model` → polymorphic (any model)

### **10.3 Status Fields**
Most entities have status fields:
- Projects: active, planning, on_hold, completed, cancelled
- PurchaseRequests: draft, submitted, approved, rejected, cancelled
- Quotations: pending, accepted, rejected, cancelled
- PurchaseOrders: draft, pending, approved, completed, cancelled
- GoodsReceipts: draft, pending, approved, rejected, cancelled
- GoodsReturns: draft, pending, approved, cancelled
- MaterialIssuances: draft, pending, approved, issued, cancelled
- InventoryItems: active, inactive
- Suppliers: active, inactive

### **10.4 Project Code Propagation**
Project codes are propagated through the system:
- Projects → PurchaseRequests → Quotations → PurchaseOrders → GoodsReceipts → GoodsReturns

### **10.5 Stock Tracking**
Stock levels are calculated dynamically from `StockMovement` records:
- Current stock = latest `balance_after` from StockMovement for the item
- All stock changes are recorded as StockMovement entries
- Movement types track direction (in/out) and reason (stock/adjustment/return)

---

## **11. DATABASE CONSTRAINTS & INDEXES**

### **Important Foreign Keys:**
- All `*_id` fields are foreign keys
- `project_code` fields are denormalized for reporting (not FK)
- Polymorphic relationships use `*_type` and `*_id` fields

### **Key Indexes (recommended):**
- `users.role_id`
- `projects.project_manager_id`
- `projects.project_code` (unique)
- `purchase_requests.project_id`
- `purchase_orders.purchase_request_id`
- `purchase_orders.supplier_id`
- `goods_receipts.purchase_order_id`
- `stock_movements.inventory_item_id`
- `stock_movements.reference_type, reference_id`
- All status fields for filtering

---

## **12. SUMMARY OF ENTITIES**

### **Core Entities (19 total):**
1. **Role** - User roles and permissions
2. **User** - System users
3. **Project** - Construction projects
4. **ChangeOrder** - Project modifications
5. **InventoryItem** - Stock items
6. **Supplier** - Suppliers/vendors
7. **SupplierPrice** - Supplier pricing
8. **PurchaseRequest** - Material requests
9. **PurchaseRequestItem** - PR line items
10. **Quotation** - Supplier quotes
11. **QuotationItem** - Quotation line items
12. **PurchaseOrder** - Purchase orders
13. **PurchaseOrderItem** - PO line items
14. **GoodsReceipt** - Receiving records
15. **GoodsReceiptItem** - Receipt line items
16. **GoodsReturn** - Return records
17. **GoodsReturnItem** - Return line items
18. **MaterialIssuance** - Material issues to projects
19. **MaterialIssuanceItem** - Issuance line items
20. **StockMovement** - Inventory movement history
21. **AuditLog** - System audit trail

---

**End of ERD Documentation**
