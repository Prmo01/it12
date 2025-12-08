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
            // Drop foreign key constraint first
            $table->dropForeign(['fabrication_job_id']);
            // Remove fabrication_job_id column
            $table->dropColumn('fabrication_job_id');
            
            // Add new enhancement fields
            $table->string('work_order_number')->nullable()->after('issuance_number');
            $table->enum('issuance_type', ['project', 'maintenance', 'general', 'repair', 'other'])->default('project')->after('work_order_number');
            
            // Add index for better query performance
            $table->index('issuance_type');
            $table->index('work_order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_issuances', function (Blueprint $table) {
            // Remove new fields
            $table->dropIndex(['issuance_type']);
            $table->dropIndex(['work_order_number']);
            $table->dropColumn(['work_order_number', 'issuance_type']);
            
            // Restore fabrication_job_id (without foreign key constraint - will be added by separate migration if needed)
            $table->foreignId('fabrication_job_id')->nullable()->after('project_id');
        });
    }
};
