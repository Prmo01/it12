<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('notes');
        });
        
        // Update status enum to include 'cancelled'
        // PostgreSQL-compatible syntax
        \DB::statement("
            DO \$\$
            DECLARE
                constraint_name TEXT;
            BEGIN
                -- Find the existing check constraint on status column
                SELECT conname INTO constraint_name
                FROM pg_constraint c
                JOIN pg_class t ON c.conrelid = t.oid
                JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(c.conkey)
                WHERE t.relname = 'purchase_requests'
                AND a.attname = 'status'
                AND c.contype = 'c'
                LIMIT 1;
                
                -- Drop existing check constraint if found
                IF constraint_name IS NOT NULL THEN
                    EXECUTE 'ALTER TABLE purchase_requests DROP CONSTRAINT ' || quote_ident(constraint_name);
                END IF;
                
                -- Add new check constraint with 'cancelled' option
                ALTER TABLE purchase_requests ADD CONSTRAINT purchase_requests_status_check 
                CHECK (status IN ('draft', 'submitted', 'approved', 'rejected', 'converted_to_po', 'cancelled'));
            END \$\$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });
        
        // Revert status enum
        // PostgreSQL-compatible syntax
        \DB::statement("
            DO \$\$
            DECLARE
                constraint_name TEXT;
            BEGIN
                -- Find the existing check constraint on status column
                SELECT conname INTO constraint_name
                FROM pg_constraint c
                JOIN pg_class t ON c.conrelid = t.oid
                JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(c.conkey)
                WHERE t.relname = 'purchase_requests'
                AND a.attname = 'status'
                AND c.contype = 'c'
                LIMIT 1;
                
                -- Drop existing check constraint if found
                IF constraint_name IS NOT NULL THEN
                    EXECUTE 'ALTER TABLE purchase_requests DROP CONSTRAINT ' || quote_ident(constraint_name);
                END IF;
                
                -- Add original check constraint without 'cancelled'
                ALTER TABLE purchase_requests ADD CONSTRAINT purchase_requests_status_check 
                CHECK (status IN ('draft', 'submitted', 'approved', 'rejected', 'converted_to_po'));
            END \$\$;
        ");
    }
};
