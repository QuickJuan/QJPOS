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
            // Drop the existing foreign key constraint
            $table->dropForeign(['order_id']);

            // Add the new foreign key constraint with cascade delete
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Drop the cascade foreign key constraint
            $table->dropForeign(['order_id']);

            // Restore the original foreign key constraint without cascade
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }
};
