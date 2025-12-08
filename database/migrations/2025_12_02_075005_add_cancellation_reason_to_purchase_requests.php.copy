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
        \DB::statement("ALTER TABLE purchase_requests MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'converted_to_po', 'cancelled') DEFAULT 'draft'");
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
        \DB::statement("ALTER TABLE purchase_requests MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'converted_to_po') DEFAULT 'draft'");
    }
};
