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
        Schema::table('material_issuances', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('notes');
        });
        
        // Update status enum to include 'cancelled'
        // PostgreSQL-compatible syntax: Drop existing constraint and add new one
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
                WHERE t.relname = 'material_issuances'
                AND a.attname = 'status'
                AND c.contype = 'c'
                LIMIT 1;
                
                -- Drop existing check constraint if found
                IF constraint_name IS NOT NULL THEN
                    EXECUTE 'ALTER TABLE material_issuances DROP CONSTRAINT ' || quote_ident(constraint_name);
                END IF;
                
                -- Add new check constraint with 'cancelled' option
                ALTER TABLE material_issuances ADD CONSTRAINT material_issuances_status_check 
                CHECK (status IN ('draft', 'approved', 'issued', 'completed', 'cancelled'));
            END \$\$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_issuances', function (Blueprint $table) {
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
                WHERE t.relname = 'material_issuances'
                AND a.attname = 'status'
                AND c.contype = 'c'
                LIMIT 1;
                
                -- Drop existing check constraint if found
                IF constraint_name IS NOT NULL THEN
                    EXECUTE 'ALTER TABLE material_issuances DROP CONSTRAINT ' || quote_ident(constraint_name);
                END IF;
                
                -- Add original check constraint without 'cancelled'
                ALTER TABLE material_issuances ADD CONSTRAINT material_issuances_status_check 
                CHECK (status IN ('draft', 'approved', 'issued', 'completed'));
            END \$\$;
        ");
    }
};
