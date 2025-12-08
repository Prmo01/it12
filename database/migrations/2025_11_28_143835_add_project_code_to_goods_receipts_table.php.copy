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
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('project_code')->nullable()->after('gr_number');
            $table->index('project_code');
        });

        // Populate project_code from purchase_order -> purchase_request -> project relationship
        \DB::statement('
            UPDATE goods_receipts 
            INNER JOIN purchase_orders ON goods_receipts.purchase_order_id = purchase_orders.id
            INNER JOIN purchase_requests ON purchase_orders.purchase_request_id = purchase_requests.id
            INNER JOIN projects ON purchase_requests.project_id = projects.id
            SET goods_receipts.project_code = projects.project_code
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropIndex(['project_code']);
            $table->dropColumn('project_code');
        });
    }
};
