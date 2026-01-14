# Table Columns Reference with Relationships
## Construction & Fabrication ERP System

Simple reference of all tables, their column names, and relationships.

**Legend:**
- `→` = belongsTo (Foreign Key)
- `←` = hasMany (Reverse Foreign Key)
- `↔` = Many-to-Many or Through relationship
- **1:1** = One-to-One relationship
- **1:N** = One-to-Many relationship
- **N:M** = Many-to-Many relationship
- **Polymorphic** = Polymorphic relationship

---

## users
- id
- name
- email
- email_verified_at
- password
- role_id → **roles.id**
- remember_token
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → roles (role_id) **1:N** (Many users belong to one role)
- ← projects (project_manager_id, client_id) **1:N** (One user can manage/own many projects)
- ← purchase_requests (requested_by, approved_by) **1:N** (One user can create/approve many PRs)
- ← purchase_orders (created_by, approved_by) **1:N** (One user can create/approve many POs)
- ← goods_receipts (received_by, approved_by) **1:N** (One user can receive/approve many GRs)
- ← goods_returns (returned_by, approved_by) **1:N** (One user can return/approve many returns)
- ← material_issuances (requested_by, approved_by, issued_by) **1:N** (One user can request/approve/issue many issuances)
- ← stock_movements (created_by) **1:N** (One user can create many movements)
- ← audit_logs (user_id) **1:N** (One user can have many audit logs)
- ← change_orders (requested_by, approved_by) **1:N** (One user can request/approve many change orders)

## roles
- id
- name
- slug
- description
- created_at
- updated_at
- deleted_at

**Relationships:**
- ← users (role_id) **1:N** (One role has many users)

## projects
- id
- project_code
- name
- description
- client_id → **users.id**
- project_manager_id → **users.id**
- start_date
- end_date
- actual_end_date
- status
- budget
- actual_cost
- progress_percentage
- notes
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → users (client_id, project_manager_id) **N:1** (Many projects belong to one user as client/manager)
- ← change_orders (project_id) **1:N** (One project has many change orders)
- ← purchase_requests (project_id) **1:N** (One project has many purchase requests)
- ← material_issuances (project_id) **1:N** (One project has many material issuances)

## change_orders
- id
- project_id → **projects.id**
- change_order_number
- description
- reason
- additional_days
- additional_cost
- status
- requested_by → **users.id**
- approved_by → **users.id**
- approved_at
- approval_notes
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → projects (project_id)
- → users (requested_by, approved_by)

## suppliers
- id
- code
- name
- contact_person
- email
- phone
- address
- tax_id
- status
- notes
- created_at
- updated_at
- deleted_at

**Relationships:**
- ← supplier_prices (supplier_id) **1:N** (One supplier has many prices)
- ← quotations (supplier_id) **1:N** (One supplier has many quotations)
- ← quotation_items (supplier_id) **1:N** (One supplier has many quotation items)
- ← purchase_orders (supplier_id) **1:N** (One supplier has many purchase orders)
- ← purchase_order_items (supplier_id) **1:N** (One supplier has many PO items)

## supplier_prices
- id
- supplier_id → **suppliers.id**
- inventory_item_id → **inventory_items.id**
- unit_price
- effective_date
- expiry_date
- notes
- created_at
- updated_at

**Relationships:**
- → suppliers (supplier_id) **N:1** (Many prices belong to one supplier)
- → inventory_items (inventory_item_id) **N:1** (Many prices belong to one item)
- **N:M** (Many suppliers can have prices for many items, unique constraint on supplier_id + inventory_item_id)

## inventory_items
- id
- item_code
- name
- description
- category
- unit_of_measure
- unit_cost
- reorder_level
- reorder_quantity
- item_type
- status
- created_at
- updated_at
- deleted_at

**Relationships:**
- ← supplier_prices (inventory_item_id) **1:N** (One item has many supplier prices)
- ← purchase_request_items (inventory_item_id) **1:N** (One item can be in many PR items)
- ← quotation_items (inventory_item_id) **1:N** (One item can be in many quotation items)
- ← purchase_order_items (inventory_item_id) **1:N** (One item can be in many PO items)
- ← goods_receipt_items (inventory_item_id) **1:N** (One item can be in many GR items)
- ← goods_return_items (inventory_item_id) **1:N** (One item can be in many return items)
- ← material_issuance_items (inventory_item_id) **1:N** (One item can be in many issuance items)
- ← stock_movements (inventory_item_id) **1:N** (One item has many stock movements)

## purchase_requests
- id
- pr_number
- project_id → **projects.id**
- purpose
- status
- requested_by → **users.id**
- approved_by → **users.id**
- approved_at
- notes
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → projects (project_id) **N:1** (Many PRs belong to one project)
- → users (requested_by, approved_by) **N:1** (Many PRs belong to one user)
- ← purchase_request_items (purchase_request_id) **1:N** (One PR has many items)
- ← quotations (purchase_request_id) **1:N** (One PR has many quotations)
- ← purchase_orders (purchase_request_id) **1:N** (One PR has many purchase orders)

## purchase_request_items
- id
- purchase_request_id → **purchase_requests.id**
- inventory_item_id → **inventory_items.id**
- quantity
- unit_cost
- specifications
- created_at
- updated_at

**Relationships:**
- → purchase_requests (purchase_request_id) **N:1** (Many items belong to one PR)
- → inventory_items (inventory_item_id) **N:1** (Many PR items reference one inventory item)

## quotations
- id
- quotation_number
- purchase_request_id → **purchase_requests.id**
- supplier_id → **suppliers.id** (nullable)
- quotation_date
- valid_until
- status
- total_amount
- project_code
- terms_conditions
- notes
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → purchase_requests (purchase_request_id) **N:1** (Many quotations belong to one PR)
- → suppliers (supplier_id) - nullable **N:1** (Many quotations belong to one supplier)
- ← quotation_items (quotation_id) **1:N** (One quotation has many items)
- ← purchase_orders (quotation_id) **1:N** (One quotation can create many POs)

## quotation_items
- id
- quotation_id → **quotations.id**
- inventory_item_id → **inventory_items.id**
- supplier_id → **suppliers.id** (nullable)
- quantity
- unit_price
- total_price
- specifications
- created_at
- updated_at

**Relationships:**
- → quotations (quotation_id) **N:1** (Many items belong to one quotation)
- → inventory_items (inventory_item_id) **N:1** (Many quotation items reference one inventory item)
- → suppliers (supplier_id) - nullable **N:1** (Many items belong to one supplier)

## purchase_orders
- id
- po_number
- purchase_request_id → **purchase_requests.id** (nullable)
- quotation_id → **quotations.id** (nullable)
- supplier_id → **suppliers.id** (nullable)
- project_code
- po_date
- expected_delivery_date
- status
- subtotal
- tax_amount
- total_amount
- terms_conditions
- delivery_address
- created_by → **users.id**
- approved_by → **users.id**
- approved_at
- notes
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → purchase_requests (purchase_request_id) - nullable **N:1** (Many POs belong to one PR)
- → quotations (quotation_id) - nullable **N:1** (Many POs belong to one quotation)
- → suppliers (supplier_id) - nullable **N:1** (Many POs belong to one supplier)
- → users (created_by, approved_by) **N:1** (Many POs belong to one user)
- ← purchase_order_items (purchase_order_id) **1:N** (One PO has many items)
- ← goods_receipts (purchase_order_id) **1:N** (One PO has many goods receipts)

## purchase_order_items
- id
- purchase_order_id → **purchase_orders.id**
- inventory_item_id → **inventory_items.id**
- supplier_id → **suppliers.id** (nullable)
- quantity
- unit_price
- total_price
- received_quantity
- specifications
- created_at
- updated_at

**Relationships:**
- → purchase_orders (purchase_order_id) **N:1** (Many items belong to one PO)
- → inventory_items (inventory_item_id) **N:1** (Many PO items reference one inventory item)
- → suppliers (supplier_id) - nullable **N:1** (Many items belong to one supplier)
- ← goods_receipt_items (purchase_order_item_id) **1:N** (One PO item can be received multiple times)

## goods_receipts
- id
- gr_number
- purchase_order_id → **purchase_orders.id**
- project_code
- gr_date
- status
- delivery_note_number
- remarks
- received_by → **users.id**
- approved_by → **users.id**
- approved_at
- rejected_at
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → purchase_orders (purchase_order_id) **N:1** (Many GRs belong to one PO)
- → users (received_by, approved_by) **N:1** (Many GRs belong to one user)
- ← goods_receipt_items (goods_receipt_id) **1:N** (One GR has many items)
- ← goods_returns (goods_receipt_id) **1:N** (One GR can have many returns)

## goods_receipt_items
- id
- goods_receipt_id → **goods_receipts.id**
- purchase_order_item_id → **purchase_order_items.id**
- inventory_item_id → **inventory_items.id**
- quantity_ordered
- quantity_received
- quantity_accepted
- quantity_rejected
- rejection_reason
- created_at
- updated_at

**Relationships:**
- → goods_receipts (goods_receipt_id) **N:1** (Many items belong to one GR)
- → purchase_order_items (purchase_order_item_id) **N:1** (Many GR items reference one PO item)
- → inventory_items (inventory_item_id) **N:1** (Many GR items reference one inventory item)
- ← goods_return_items (goods_receipt_item_id) **1:N** (One GR item can be returned multiple times)

## goods_returns
- id
- return_number
- goods_receipt_id → **goods_receipts.id**
- project_code
- return_date
- status
- reason
- returned_by → **users.id**
- approved_by → **users.id**
- approved_at
- notes
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → goods_receipts (goods_receipt_id) **N:1** (Many returns belong to one GR)
- → users (returned_by, approved_by) **N:1** (Many returns belong to one user)
- ← goods_return_items (goods_return_id) **1:N** (One return has many items)

## goods_return_items
- id
- goods_return_id → **goods_returns.id**
- goods_receipt_item_id → **goods_receipt_items.id**
- inventory_item_id → **inventory_items.id**
- quantity
- reason
- created_at
- updated_at

**Relationships:**
- → goods_returns (goods_return_id) **N:1** (Many items belong to one return)
- → goods_receipt_items (goods_receipt_item_id) **N:1** (Many return items reference one GR item)
- → inventory_items (inventory_item_id) **N:1** (Many return items reference one inventory item)

## material_issuances
- id
- issuance_number
- project_id → **projects.id** (nullable)
- issuance_type
- work_order_number
- issuance_date
- status
- purpose
- requested_by → **users.id**
- approved_by → **users.id**
- issued_by → **users.id**
- approved_at
- issued_at
- notes
- cancellation_reason
- created_at
- updated_at
- deleted_at

**Relationships:**
- → projects (project_id) - nullable **N:1** (Many issuances belong to one project)
- → users (requested_by, approved_by, issued_by) **N:1** (Many issuances belong to one user)
- ← material_issuance_items (material_issuance_id) **1:N** (One issuance has many items)

## material_issuance_items
- id
- material_issuance_id → **material_issuances.id**
- inventory_item_id → **inventory_items.id**
- quantity
- unit_cost
- notes
- created_at
- updated_at

**Relationships:**
- → material_issuances (material_issuance_id) **N:1** (Many items belong to one issuance)
- → inventory_items (inventory_item_id) **N:1** (Many issuance items reference one inventory item)

## stock_movements
- id
- inventory_item_id → **inventory_items.id**
- movement_type
- reference_type (polymorphic)
- reference_id (polymorphic)
- quantity
- unit_cost
- balance_after
- notes
- created_by → **users.id**
- created_at
- updated_at

**Relationships:**
- → inventory_items (inventory_item_id) **N:1** (Many movements belong to one item)
- → users (created_by) **N:1** (Many movements belong to one user)
- ↔ **Polymorphic** reference (reference_type, reference_id) - can reference GoodsReceipt, GoodsReturn, MaterialIssuance, etc. **N:1** (Many movements can reference one source record)

## audit_logs
- id
- model_type (polymorphic)
- model_id (polymorphic)
- action
- old_values
- new_values
- description
- user_id → **users.id**
- ip_address
- user_agent
- created_at
- updated_at

**Relationships:**
- → users (user_id) **N:1** (Many logs belong to one user)
- ↔ **Polymorphic** reference (model_type, model_id) - can reference any model in the system **N:1** (Many logs can reference one model record)

