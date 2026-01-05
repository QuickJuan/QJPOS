<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('void_items', function (Blueprint $table) {
            // Check if cart_id foreign key exists before trying to drop it
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_NAME = 'void_items'
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME = 'void_items_cart_id_foreign'
                AND TABLE_SCHEMA = DATABASE()
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign(['cart_id']);
            }

            // Drop cart_id column if it exists
            if (Schema::hasColumn('void_items', 'cart_id')) {
                $table->dropColumn('cart_id');
            }

            // Add new columns only if they don't exist
            if (!Schema::hasColumn('void_items', 'cashier_id')) {
                $table->unsignedBigInteger('cashier_id')->nullable()->after('voided_by');
                $table->foreign('cashier_id')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('void_items', 'cashier_session_id')) {
                $table->unsignedBigInteger('cashier_session_id')->nullable()->after('cashier_id');
                $table->foreign('cashier_session_id')->references('id')->on('cashier_sessions')->onDelete('set null');
            }

            // Drop columns that are no longer needed
            $columnsToDrop = ['discount_id', 'discount_amount', 'vatable_sales', 'vat_exempt_sales',
                'vat_amount', 'non_vat_sales', 'less_tax', 'discount', 'coupon_code', 'notes',
                'meta_data', 'tax_type', 'tax_percentage', 'tax_included', 'serving_priority',
                'served_at', 'duration_in_seconds'];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('void_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
    /*
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('void_items', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['cashier_id']);
            $table->dropForeign(['cashier_session_id']);

            // Drop new columns
            $table->dropColumn(['cashier_id', 'cashier_session_id']);

            // Re-add removed columns
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('vatable_sales', 15, 2)->default(0);
            $table->decimal('vat_exempt_sales', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('non_vat_sales', 15, 2)->default(0);
            $table->decimal('less_tax', 15, 2)->default(0);
            $table->string('discount')->nullable();
            $table->string('coupon_code')->nullable();
            $table->longText('notes')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamp('served_time')->nullable();
            $table->string('tax_type')->nullable();
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->boolean('tax_included')->default(false);
            $table->integer('serving_priority')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->integer('duration_in_seconds')->nullable();
        });
    }
};
