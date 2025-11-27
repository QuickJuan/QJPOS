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
        // Add columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('invoice_no')->nullable()->after('id');
            $table->integer('bill_no')->nullable()->after('invoice_no');
            $table->foreignId('discount_id')->nullable()->after('table_room_id')->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->after('discount_id')->constrained('coupon_codes')->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('coupon_id');
            $table->decimal('total_amount', 10, 2)->nullable()->after('coupon_code');
            $table->decimal('total_discount', 10, 2)->nullable()->default(0)->after('total_amount');
            $table->decimal('item_discount', 10, 2)->nullable()->default(0)->after('total_discount');
            $table->decimal('total_due', 10, 2)->nullable()->after('item_discount');
            $table->decimal('amount_tendered', 10, 2)->nullable()->after('total_due');
        });

        // Add columns to carts table
        Schema::table('carts', function (Blueprint $table) {
            $table->integer('invoice_no')->nullable()->after('id');
            $table->integer('bill_no')->nullable()->after('invoice_no');
            $table->foreignId('discount_id')->nullable()->after('table_room_id')->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->after('discount_id')->constrained('coupon_codes')->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('coupon_id');
            $table->decimal('total_amount', 10, 2)->nullable()->after('coupon_code');
            $table->decimal('total_discount', 10, 2)->nullable()->default(0)->after('total_amount');
            $table->decimal('item_discount', 10, 2)->nullable()->default(0)->after('total_discount')    ;
            $table->decimal('total_due', 10, 2)->nullable()->after('item_discount');
            $table->decimal('amount_tendered', 10, 2)->nullable()->after('total_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn([
                'discount_id',
                'coupon_id',
                'coupon_code',
                'total_amount',
                'total_discount',
                'item_discount',
                'total_due',
                'amount_tendered',
            ]);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn([
                'discount_id',
                'coupon_id',
                'coupon_code',
                'total_amount',
                'total_discount',
                'item_discount',
                'total_due',
                'amount_tendered',
            ]);
        });
    }
};
