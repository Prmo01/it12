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
        Schema::table('change_orders', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('approval_notes');
        });
        
        // Update status enum to include 'cancelled'
        \DB::statement("ALTER TABLE change_orders MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('change_orders', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });
        
        // Revert status enum
        \DB::statement("ALTER TABLE change_orders MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
