# Entity Relationship Diagram (ERD)
## Construction & Fabrication ERP System

---

## **Core Entities & Relationships**

### **1. USER MANAGEMENT**

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
                              │
                              │ 1:N (project_manager_id)
                              ▼
                        ┌─────────────┐
                        │   Project   │
                        └─────────────┘
```

**Relationships:**
- `User` belongsTo `Role` (role_id)
- `User` hasMany `Project` (as project_manager_id)
- `User` hasMany `Project` (as client_id)

---

### **2. PROJECT MANAGEMENT**

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
└─────────────┘
      │
      │ 1:N
      ▼
┌─────────────┐
│Material     │
│Issuance     │
└─────────────┘
```

**Relationships:**
- `Project` belongsTo `User` (project_manager_id)
- `Project` hasMany `ChangeOrder`
- `Project` hasMany `PurchaseRequest`
- `Project` hasMany `MaterialIssuance`
- `ChangeOrder` belongsTo `Project`
- `ChangeOrder` belongsTo `User` (requested_by, approved_by)

---

### **3. PROCUREMENT WORKFLOW**

```
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
│ cancellation│
│   _reason   │
│ timestamps  │
│ deleted_at  │
└─────────────┘
      │
      │ 1:N
      ├─────────────────┐
      │                 │
      ▼                 ▼
┌─────────────┐   ┌─────────────┐
│Purchase     │   │ Quotation   │
│RequestItem  │   │─────────────│
│─────────────│   │ id (PK)     │
│ id (PK)     │   │ quotation_  │
│ purchase_   │   │   number    │
│  request_id │   │ project_code│
│ inventory_  │   │ purchase_   │
│  item_id    │   │  request_id │
│ quantity    │   │ supplier_id │
│ unit_cost   │   │ quotation_  │
│ specifica-  │   │   date      │
│   tions     │   │ valid_until │
│ timestamps  │   │ status      │
└─────────────┘   │ total_amount│
                  │ terms_       │
                  │  conditions │
                  │ notes       │
                  │ cancellation│
                  │   _reason   │
                  │ timestamps  │
                  │ deleted_at  │
                  └─────────────┘
                        │
                        │ 1:N
                        ├─────────────────┐
                        │                 │
                        ▼                 ▼
                  ┌─────────────┐   ┌─────────────┐
                  │Quotation    │   │Purchase     │
                  │Item         │   │Order        │
                  │─────────────│   │─────────────│
                  │ id (PK)     │   │ id (PK)     │
                  │ quotation_id│   │ po_number   │
                  │ inventory_  │   │ project_code│
                  │  item_id    │   │ purchase_   │
                  │ supplier_id │   │  request_id │
                  │ quantity    │   │ quotation_id│
                  │ unit_price  │   │ supplier_id │
                  │ total_price │   │ po_date     │
                  │ specifica-  │   │ expected_   │
                  │   tions     │   │  delivery_  │
                  │ timestamps  │   │   date      │
                  └─────────────┘   │ status      │
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
                                    │ cancellation│
                                    │   _reason   │
                                    │ timestamps  │
                                    │ deleted_at  │
                                    └─────────────┘
                                          │
                                          │ 1:N
                                          ├─────────────────┐
                                          │                 │
                                          ▼                 ▼
                                    ┌─────────────┐   ┌─────────────┐
                                    │Purchase     │   │GoodsReceipt │
                                    │OrderItem    │   │─────────────│
                                    │─────────────│   │ id (PK)     │
                                    │ id (PK)     │   │ gr_number   │
                                    │ purchase_   │   │ project_code│
                                    │  order_id   │   │ purchase_   │
                                    │ inventory_  │   │  order_id   │
                                    │  item_id    │   │ gr_date     │
                                    │ supplier_id │   │ status      │
                                    │ quantity    │   │ delivery_   │
                                    │ unit_price  │   │  note_number│
                                    │ total_price │   │ remarks     │
                                    │ specifica-  │   │ received_by │
                                    │   tions     │   │ approved_by │
                                    │ timestamps  │   │ approved_at │
                                    └─────────────┘   │ cancellation│
                                                      │   _reason   │
                                                      │ timestamps  │
                                                      │ deleted_at  │
                                                      └─────────────┘
                                                            │
                                                            │ 1:N
                                                            ├─────────────────┐
                                                            │                 │
                                                            ▼                 ▼
                                                      ┌─────────────┐   ┌─────────────┐
                                                      │GoodsReceipt │   │GoodsReturn  │
                                                      │Item         │   │─────────────│
                                                      │─────────────│   │ id (PK)     │
                                                      │ id (PK)     │   │ return_     │
                                                      │ goods_      │   │   number    │
                                                      │  receipt_id │   │ project_code│
                                                      │ inventory_  │   │ goods_      │
                                                      │  item_id    │   │  receipt_id │
                                                      │ quantity    │   │ return_date │
                                                      │ unit_cost   │   │ status      │
                                                      │ received_   │   │ reason      │
                                                      │  quantity   │   │ returned_by │
                                                      │ timestamps  │   │ approved_by │
                                                      └─────────────┘   │ approved_at │
                                                                        │ notes       │
                                                                        │ cancellation│
                                                                        │   _reason   │
                                                                        │ timestamps  │
                                                                        │ deleted_at  │
                                                                        └─────────────┘
                                                                              │
                                                                              │ 1:N
                                                                              ▼
                                                                        ┌─────────────┐
                                                                        │GoodsReturn  │
                                                                        │Item         │
                                                                        │─────────────│
                                                                        │ id (PK)     │
                                                                        │ goods_      │
                                                                        │  return_id │
                                                                        │ inventory_  │
                                                                        │  item_id    │
                                                                        │ quantity    │
                                                                        │ unit_cost   │
                                                                        │ reason      │
                                                                        │ timestamps  │
                                                                        └─────────────┘
```

**Relationships:**
- `PurchaseRequest` belongsTo `Project`
- `PurchaseRequest` belongsTo `User` (requested_by, approved_by)
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
- `QuotationItem` belongsTo `InventoryItem`
- `QuotationItem` belongsTo `Supplier`
- `PurchaseOrder` belongsTo `PurchaseRequest`
- `PurchaseOrder` belongsTo `Quotation`
- `PurchaseOrder` belongsTo `Supplier`
- `PurchaseOrder` belongsTo `User` (created_by, approved_by)
- `PurchaseOrder` hasMany `PurchaseOrderItem`
- `PurchaseOrder` hasMany `GoodsReceipt`
- `PurchaseOrderItem` belongsTo `PurchaseOrder`
- `PurchaseOrderItem` belongsTo `InventoryItem`
- `PurchaseOrderItem` belongsTo `Supplier`
- `GoodsReceipt` belongsTo `PurchaseOrder`
- `GoodsReceipt` belongsTo `User` (received_by, approved_by)
- `GoodsReceipt` hasMany `GoodsReceiptItem`
- `GoodsReceipt` hasMany `GoodsReturn`
- `GoodsReceiptItem` belongsTo `GoodsReceipt`
- `GoodsReceiptItem` belongsTo `InventoryItem`
- `GoodsReturn` belongsTo `GoodsReceipt`
- `GoodsReturn` belongsTo `User` (returned_by, approved_by)
- `GoodsReturn` hasMany `GoodsReturnItem`
- `GoodsReturnItem` belongsTo `GoodsReturn`
- `GoodsReturnItem` belongsTo `InventoryItem`

---

### **4. INVENTORY MANAGEMENT**

```
┌─────────────┐
│InventoryItem│
│─────────────│
│ id (PK)     │
│ item_code   │
│ name        │
│ description │
│ category    │
│ unit_of_    │
│  measure    │
│ unit_cost   │
│ reorder_    │
│  level      │
│ reorder_    │
│  quantity   │
│ item_type   │
│ status      │
│ timestamps  │
│ deleted_at  │
└─────────────┘
      │
      │ 1:N
      ├─────────────────────────────────────────────────────────────┐
      │                                                             │
      ▼                                                             ▼
┌─────────────┐                                             ┌─────────────┐
│Stock        │                                             │SupplierPrice│
│Movement     │                                             │─────────────│
│─────────────│                                             │ id (PK)     │
│ id (PK)     │                                             │ supplier_id │
│ inventory_  │                                             │ inventory_  │
│  item_id    │                                             │  item_id    │
│ movement_   │                                             │ unit_price  │
│  type       │                                             │ effective_  │
│ reference_  │                                             │  date       │
│  type       │                                             │ expiry_date │
│ reference_id│                                             │ notes       │
│ quantity    │                                             │ timestamps  │
│ unit_cost   │                                             └─────────────┘
│ balance_    │                                                     │
│  after      │                                                     │
│ notes       │                                                     │
│ created_by  │                                                     │
│ timestamps  │                                                     │
└─────────────┘                                                     │
      │                                                             │
      │ N:1                                                         │
      ▼                                                             │
┌─────────────┐                                                     │
│    User     │                                                     │
└─────────────┘                                                     │
                                                                     │
                                                                     │ N:1
                                                                     ▼
                                                              ┌─────────────┐
                                                              │  Supplier   │
                                                              │─────────────│
                                                              │ id (PK)     │
                                                              │ code        │
                                                              │ name        │
                                                              │ contact_    │
                                                              │  person     │
                                                              │ email       │
                                                              │ phone       │
                                                              │ address     │
                                                              │ tax_id      │
                                                              │ status      │
                                                              │ notes       │
                                                              │ timestamps  │
                                                              │ deleted_at  │
                                                              └─────────────┘
```

**Relationships:**
- `InventoryItem` hasMany `StockMovement`
- `InventoryItem` hasMany `PurchaseRequestItem`
- `InventoryItem` hasMany `QuotationItem`
- `InventoryItem` hasMany `PurchaseOrderItem`
- `InventoryItem` hasMany `GoodsReceiptItem`
- `InventoryItem` hasMany `GoodsReturnItem`
- `InventoryItem` hasMany `MaterialIssuanceItem`
- `InventoryItem` hasMany `SupplierPrice`
- `StockMovement` belongsTo `InventoryItem`
- `StockMovement` belongsTo `User` (created_by)
- `StockMovement` morphTo `reference` (polymorphic)
- `Supplier` hasMany `Quotation`
- `Supplier` hasMany `PurchaseOrder`
- `Supplier` hasMany `SupplierPrice`
- `SupplierPrice` belongsTo `Supplier`
- `SupplierPrice` belongsTo `InventoryItem`

---

### **5. MATERIAL ISSUANCE**

```
┌─────────────┐         ┌─────────────┐
│   Project   │◄────────│Material     │
│─────────────│         │Issuance     │
│ id (PK)     │         │─────────────│
│ ...         │         │ id (PK)     │
└─────────────┘         │ project_id  │
                        │ issuance_   │
                        │   number    │
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
                        │ cancellation│
                        │   _reason   │
                        │ timestamps  │
                        │ deleted_at  │
                        └─────────────┘
                              │
                              │ 1:N
                              ▼
                        ┌─────────────┐
                        │Material     │
                        │IssuanceItem │
                        │─────────────│
                        │ id (PK)     │
                        │ material_   │
                        │  issuance_id│
                        │ inventory_  │
                        │  item_id    │
                        │ quantity    │
                        │ unit_cost   │
                        │ notes       │
                        │ timestamps  │
                        └─────────────┘
```

**Relationships:**
- `MaterialIssuance` belongsTo `Project`
- `MaterialIssuance` belongsTo `User` (requested_by, approved_by, issued_by)
- `MaterialIssuance` hasMany `MaterialIssuanceItem`
- `MaterialIssuanceItem` belongsTo `MaterialIssuance`
- `MaterialIssuanceItem` belongsTo `InventoryItem`

---

## **Complete Entity List**

### **Core Entities (21 total):**

1. **Role** - User roles and permissions
2. **User** - System users
3. **Project** - Construction projects
4. **ChangeOrder** - Project modifications
5. **PurchaseRequest** - Material requisitions
6. **PurchaseRequestItem** - PR line items
7. **Quotation** - Supplier quotations
8. **QuotationItem** - Quotation line items
9. **PurchaseOrder** - Purchase orders
10. **PurchaseOrderItem** - PO line items
11. **GoodsReceipt** - Material receipts
12. **GoodsReceiptItem** - Receipt line items
13. **GoodsReturn** - Material returns
14. **GoodsReturnItem** - Return line items
15. **InventoryItem** - Inventory catalog
16. **StockMovement** - Stock transaction history
17. **MaterialIssuance** - Material issuances
18. **MaterialIssuanceItem** - Issuance line items
19. **Supplier** - Supplier master data
20. **SupplierPrice** - Supplier pricing
21. **AuditLog** - System audit trail (if implemented)

---

## **Key Relationships Summary**

### **One-to-Many (1:N):**
- Role → User
- User → Project (as manager)
- Project → ChangeOrder
- Project → PurchaseRequest
- Project → MaterialIssuance
- PurchaseRequest → PurchaseRequestItem
- PurchaseRequest → Quotation
- PurchaseRequest → PurchaseOrder
- Quotation → QuotationItem
- Quotation → PurchaseOrder
- PurchaseOrder → PurchaseOrderItem
- PurchaseOrder → GoodsReceipt
- GoodsReceipt → GoodsReceiptItem
- GoodsReceipt → GoodsReturn
- GoodsReturn → GoodsReturnItem
- InventoryItem → StockMovement
- InventoryItem → PurchaseRequestItem
- InventoryItem → QuotationItem
- InventoryItem → PurchaseOrderItem
- InventoryItem → GoodsReceiptItem
- InventoryItem → GoodsReturnItem
- InventoryItem → MaterialIssuanceItem
- InventoryItem → SupplierPrice
- Supplier → Quotation
- Supplier → PurchaseOrder
- Supplier → SupplierPrice
- MaterialIssuance → MaterialIssuanceItem

### **Many-to-One (N:1):**
- User → Role
- Project → User (project_manager_id)
- ChangeOrder → Project
- ChangeOrder → User (requested_by, approved_by)
- PurchaseRequest → Project
- PurchaseRequest → User (requested_by, approved_by)
- PurchaseRequestItem → PurchaseRequest
- PurchaseRequestItem → InventoryItem
- Quotation → PurchaseRequest
- Quotation → Supplier
- QuotationItem → Quotation
- QuotationItem → InventoryItem
- QuotationItem → Supplier
- PurchaseOrder → PurchaseRequest
- PurchaseOrder → Quotation
- PurchaseOrder → Supplier
- PurchaseOrder → User (created_by, approved_by)
- PurchaseOrderItem → PurchaseOrder
- PurchaseOrderItem → InventoryItem
- PurchaseOrderItem → Supplier
- GoodsReceipt → PurchaseOrder
- GoodsReceipt → User (received_by, approved_by)
- GoodsReceiptItem → GoodsReceipt
- GoodsReceiptItem → InventoryItem
- GoodsReturn → GoodsReceipt
- GoodsReturn → User (returned_by, approved_by)
- GoodsReturnItem → GoodsReturn
- GoodsReturnItem → InventoryItem
- StockMovement → InventoryItem
- StockMovement → User (created_by)
- MaterialIssuance → Project
- MaterialIssuance → User (requested_by, approved_by, issued_by)
- MaterialIssuanceItem → MaterialIssuance
- MaterialIssuanceItem → InventoryItem
- SupplierPrice → Supplier
- SupplierPrice → InventoryItem

### **Polymorphic:**
- StockMovement → reference (morphTo) - Can reference GoodsReceipt, MaterialIssuance, etc.

---

## **Data Flow Diagram**

```
PROJECT
  │
  ├─► Purchase Request
  │     │
  │     ├─► Quotation
  │     │     │
  │     │     └─► Purchase Order
  │     │           │
  │     │           └─► Goods Receipt ──► Inventory (Stock IN)
  │     │                                     │
  │     │                                     └─► Goods Return (if defective)
  │     │
  │     └─► Purchase Order (direct)
  │
  └─► Material Issuance ──► Inventory (Stock OUT)
```

---

## **Status Workflows**

### **Purchase Request:**
`draft` → `submitted` → `approved` / `rejected` / `cancelled`

### **Quotation:**
`pending` → `accepted` / `rejected` / `cancelled`

### **Purchase Order:**
`draft` → `pending` → `approved` → `completed` / `cancelled`

### **Goods Receipt:**
`draft` → `pending` → `approved` / `cancelled`

### **Goods Return:**
`draft` → `pending` → `approved` / `cancelled`

### **Material Issuance:**
`draft` → `pending` → `approved` → `issued` / `cancelled`

### **Change Order:**
`pending` → `approved` / `rejected` / `cancelled`

### **Project:**
`planning` → `active` → `completed` / `on_hold` / `cancelled`

---

This ERD represents the complete database structure of the Construction & Fabrication ERP System.

