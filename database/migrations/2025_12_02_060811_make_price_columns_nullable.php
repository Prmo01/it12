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
        // Make price columns nullable to support price-hiding feature
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->change();
            $table->decimal('total_price', 10, 2)->nullable()->change();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->change();
            $table->decimal('total_price', 10, 2)->nullable()->change();
        });

        Schema::table('material_issuance_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
        });

        Schema::table('supplier_prices', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable()->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to non-nullable with default 0
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0)->change();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->default(0)->change();
            $table->decimal('total_price', 10, 2)->default(0)->change();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->default(0)->change();
            $table->decimal('total_price', 10, 2)->default(0)->change();
        });

        Schema::table('material_issuance_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0)->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0)->change();
        });

        Schema::table('supplier_prices', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->default(0)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0)->change();
        });
    }
};
