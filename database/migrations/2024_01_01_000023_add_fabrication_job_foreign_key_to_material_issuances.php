<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_issuances', function (Blueprint $table) {
            $table->foreign('fabrication_job_id')->references('id')->on('fabrication_jobs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('material_issuances', function (Blueprint $table) {
            $table->dropForeign(['fabrication_job_id']);
        });
    }
};

