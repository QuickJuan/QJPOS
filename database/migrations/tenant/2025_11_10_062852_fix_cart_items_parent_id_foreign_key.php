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
        // This migration should be run on tenant databases
        // Use Schema::connection('tenant') or run via tenant migration
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop the wrong foreign key constraint
            $table->dropForeign(['parent_id']);

            // Add the correct foreign key constraint
            $table->foreign('parent_id')
                ->references('id')
                ->on('cart_items')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop the correct foreign key constraint
            $table->dropForeign(['parent_id']);

            // Restore the wrong foreign key constraint (for rollback)
            $table->foreign('parent_id')
                ->references('id')
                ->on('product_packagings')
                ->cascadeOnDelete();
        });
    }
};
