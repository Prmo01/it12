<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_issuances', function (Blueprint $table) {
            $table->id();
            $table->string('issuance_number')->unique();
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('fabrication_job_id')->nullable()->onDelete('set null');
            $table->date('issuance_date');
            $table->enum('status', ['draft', 'approved', 'issued', 'completed'])->default('draft');
            $table->text('purpose')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_issuances');
    }
};

