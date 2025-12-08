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
        \DB::statement("ALTER TABLE material_issuances MODIFY COLUMN status ENUM('draft', 'approved', 'issued', 'completed', 'cancelled') DEFAULT 'draft'");
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
        \DB::statement("ALTER TABLE material_issuances MODIFY COLUMN status ENUM('draft', 'approved', 'issued', 'completed') DEFAULT 'draft'");
    }
};
