<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_issuances', function (Blueprint $table) {
            $table->enum('delivery_status', ['pending', 'delivered', 'received'])->default('pending')->after('status');
            $table->foreignId('received_by')->nullable()->after('issued_by')->constrained('users')->onDelete('set null');
            $table->timestamp('received_at')->nullable()->after('received_by');
            $table->index('delivery_status');
        });
    }

    public function down(): void
    {
        Schema::table('material_issuances', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'received_by', 'received_at']);
        });
    }
};

