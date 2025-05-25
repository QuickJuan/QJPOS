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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('receipt_alias', 40)->nullable(); 
            $table->string('name', 150);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('product_type', 20)->default('simple')->nullable()->comments('simple, composite, custom, digital'); // 'product' or 'service'
            $table->boolean('is_active')->default(true);
            $table->boolean('with_expiration')->default(false);
            $table->boolean('with_serial_number')->default(false);
            $table->boolean('sellable')->default(false);
            $table->string('vat_type')->nullable()->comment('vat, non_vat');
            $table->boolean('vat_inclusive')->default(false);
            $table->integer('vat_rate')->default(0)->comment('VAT rate in percentage');
            $table->boolean('track_inventory')->default(false);
            $table->integer('minimum_stock_level')->default(0)->comment('Minimum stock level before alert');
            $table->integer('maximum_stock_level')->default(0)->comment('Maximum stock level to maintain');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
