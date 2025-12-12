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
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('placed_orderby')->nullable()->after('placed_order');
            $table->unsignedBigInteger('served_by')->nullable()->after('placed_orderby');
            $table->foreign('placed_orderby')->references('id')->on('users')->onDelete('set null');
            $table->foreign('served_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['placed_orderby']);
            $table->dropColumn('placed_orderby');

            $table->dropForeign(['served_by']);
            $table->dropColumn('served_by');
        });
    }
};
