<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('event_type'); // created, status_changed, updated, milestone, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('old_value')->nullable(); // For status changes, old status
            $table->string('new_value')->nullable(); // For status changes, new status
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('reference_type')->nullable(); // Model class name
            $table->unsignedBigInteger('reference_id')->nullable(); // Related record ID
            $table->timestamps();
            
            $table->index('project_id');
            $table->index('event_type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_histories');
    }
};

