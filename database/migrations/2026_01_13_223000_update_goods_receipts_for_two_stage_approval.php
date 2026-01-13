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
            DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN status ENUM('draft', 'pending', 'warehouse_approved', 'approved', 'rejected', 'cancelled') DEFAULT 'draft'");
            
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
            DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'rejected', 'cancelled') DEFAULT 'draft'");
        });
    }
};
