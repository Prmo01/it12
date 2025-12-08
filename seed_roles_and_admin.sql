-- Seed Roles
INSERT INTO roles (name, slug, description, created_at, updated_at) 
VALUES 
    ('Admin', 'admin', 'Full system access - Can manage all modules, users, and system settings', NOW(), NOW()),
    ('Inventory Manager', 'inventory_manager', 'Manages inventory, goods receipts, goods returns, and material issuance', NOW(), NOW()),
    ('Purchasing', 'purchasing', 'Handles procurement - Purchase requests, quotations, purchase orders, and suppliers', NOW(), NOW()),
    ('Project Manager', 'project_manager', 'Manages projects and change orders', NOW(), NOW()),
    ('Warehouse Manager', 'warehouse_manager', 'Manages warehouse operations, inspects goods before approval and flags defective items', NOW(), NOW())
ON CONFLICT (slug) DO UPDATE SET
    name = EXCLUDED.name,
    description = EXCLUDED.description,
    updated_at = NOW();

-- Seed Admin User
-- Password: password (bcrypt hashed)
INSERT INTO users (name, email, password, role_id, created_at, updated_at)
SELECT 
    'Admin User',
    'admin@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- bcrypt hash of "password"
    r.id,
    NOW(),
    NOW()
FROM roles r
WHERE r.slug = 'admin'
ON CONFLICT (email) DO UPDATE SET
    name = EXCLUDED.name,
    password = EXCLUDED.password,
    role_id = EXCLUDED.role_id,
    updated_at = NOW();
