<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_return_id')->constrained('goods_returns')->onDelete('cascade');
            $table->foreignId('goods_receipt_item_id')->constrained('goods_receipt_items')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_return_items');
    }
};

