-- PostgreSQL Queries for 2026/01/14 Migrations
-- Migration 1: Add user fields to quotations table
-- Migration 2: Add warehouse validation to material_issuances table
-- Migration 3: Create project_histories table

-- ============================================================================
-- Migration 1: 2026_01_14_100000_add_user_fields_to_quotations_table
-- ============================================================================

-- Add created_by column to quotations table
ALTER TABLE quotations 
ADD COLUMN created_by BIGINT NULL;

-- Add foreign key constraint
ALTER TABLE quotations 
ADD CONSTRAINT quotations_created_by_foreign 
FOREIGN KEY (created_by) 
REFERENCES users(id) 
ON DELETE SET NULL;

-- Add index on created_by
CREATE INDEX quotations_created_by_index ON quotations(created_by);

-- Note: The 'after' clause is not supported in PostgreSQL ALTER TABLE
-- If you need to position the column, you would need to recreate the table
-- or use a view. The column will be added at the end of the table.

-- ============================================================================
-- Migration 2: 2026_01_14_100001_add_warehouse_validation_to_material_issuances_table
-- ============================================================================

-- Create ENUM type for delivery_status (if it doesn't exist)
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'delivery_status_enum') THEN
        CREATE TYPE delivery_status_enum AS ENUM ('pending', 'delivered', 'received');
    END IF;
END $$;

-- Add delivery_status column with default value
ALTER TABLE material_issuances 
ADD COLUMN delivery_status delivery_status_enum NOT NULL DEFAULT 'pending';

-- Add received_by column
ALTER TABLE material_issuances 
ADD COLUMN received_by BIGINT NULL;

-- Add foreign key constraint for received_by
ALTER TABLE material_issuances 
ADD CONSTRAINT material_issuances_received_by_foreign 
FOREIGN KEY (received_by) 
REFERENCES users(id) 
ON DELETE SET NULL;

-- Add received_at column
ALTER TABLE material_issuances 
ADD COLUMN received_at TIMESTAMP NULL;

-- Add index on delivery_status
CREATE INDEX material_issuances_delivery_status_index ON material_issuances(delivery_status);

-- ============================================================================
-- Migration 3: 2026_01_14_100002_create_project_history_table
-- ============================================================================

-- Create project_histories table
CREATE TABLE IF NOT EXISTS project_histories (
    id BIGSERIAL PRIMARY KEY,
    project_id BIGINT NOT NULL,
    event_type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    old_value VARCHAR(255) NULL,
    new_value VARCHAR(255) NULL,
    user_id BIGINT NULL,
    reference_type VARCHAR(255) NULL,
    reference_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Foreign key constraints
    CONSTRAINT project_histories_project_id_foreign 
        FOREIGN KEY (project_id) 
        REFERENCES projects(id) 
        ON DELETE CASCADE,
    
    CONSTRAINT project_histories_user_id_foreign 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE SET NULL
);

-- Create indexes
CREATE INDEX project_histories_project_id_index ON project_histories(project_id);
CREATE INDEX project_histories_event_type_index ON project_histories(event_type);
CREATE INDEX project_histories_reference_type_reference_id_index ON project_histories(reference_type, reference_id);

-- ============================================================================
-- Rollback Queries (for down migrations)
-- ============================================================================

-- Rollback Migration 1: Remove user fields from quotations table
-- DROP INDEX IF EXISTS quotations_created_by_index;
-- ALTER TABLE quotations DROP CONSTRAINT IF EXISTS quotations_created_by_foreign;
-- ALTER TABLE quotations DROP COLUMN IF EXISTS created_by;

-- Rollback Migration 2: Remove warehouse validation from material_issuances table
-- DROP INDEX IF EXISTS material_issuances_delivery_status_index;
-- ALTER TABLE material_issuances DROP CONSTRAINT IF EXISTS material_issuances_received_by_foreign;
-- ALTER TABLE material_issuances DROP COLUMN IF EXISTS delivery_status;
-- ALTER TABLE material_issuances DROP COLUMN IF EXISTS received_by;
-- ALTER TABLE material_issuances DROP COLUMN IF EXISTS received_at;
-- DROP TYPE IF EXISTS delivery_status_enum;

-- Rollback Migration 3: Drop project_histories table
-- DROP TABLE IF EXISTS project_histories;

