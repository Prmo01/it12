<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // Add new status 'warehouse_approved' to enum
            \DB::statement("
                DO \$\$
                DECLARE
                    constraint_name TEXT;
                BEGIN
                    SELECT conname INTO constraint_name FROM pg_constraint c JOIN pg_class t ON c.conrelid = t.oid JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(c.conkey) WHERE t.relname = 'goods_receipts' AND a.attname = 'status' AND c.contype = 'c' LIMIT 1;
                    IF constraint_name IS NOT NULL THEN
                        EXECUTE 'ALTER TABLE goods_receipts DROP CONSTRAINT ' || quote_ident(constraint_name);
                    END IF;
                    ALTER TABLE goods_receipts ADD CONSTRAINT goods_receipts_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'pending'::text, 'warehouse_approved'::text, 'approved'::text, 'rejected'::text, 'cancelled'::text]));
                END \$\$;
            ");
            // Add inventory manager approval fields
            $table->foreignId('warehouse_approved_by')->nullable()->after('approved_by')->constrained('users')->onDelete('set null');
            $table->timestamp('warehouse_approved_at')->nullable()->after('approved_at');
            $table->foreignId('inventory_approved_by')->nullable()->after('warehouse_approved_at')->constrained('users')->onDelete('set null');
            $table->timestamp('inventory_approved_at')->nullable()->after('inventory_approved_by');
            $table->text('inventory_feedback')->nullable()->after('inventory_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['warehouse_approved_by']);
            $table->dropForeign(['inventory_approved_by']);
            $table->dropColumn(['warehouse_approved_by', 'warehouse_approved_at', 'inventory_approved_by', 'inventory_approved_at', 'inventory_feedback']);
            
            // Revert status enum
            \DB::statement("
                DO \$\$
                DECLARE
                    constraint_name TEXT;
                BEGIN
                    SELECT conname INTO constraint_name FROM pg_constraint c JOIN pg_class t ON c.conrelid = t.oid JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(c.conkey) WHERE t.relname = 'goods_receipts' AND a.attname = 'status' AND c.contype = 'c' LIMIT 1;
                    IF constraint_name IS NOT NULL THEN
                        EXECUTE 'ALTER TABLE goods_receipts DROP CONSTRAINT ' || quote_ident(constraint_name);
                    END IF;
                    ALTER TABLE goods_receipts ADD CONSTRAINT goods_receipts_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'pending'::text, 'approved'::text, 'rejected'::text, 'cancelled'::text]));
                END \$\$;
            ");        });
    }
};
