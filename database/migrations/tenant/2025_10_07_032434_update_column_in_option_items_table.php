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
        Schema::table('option_items', function (Blueprint $table) {
            $table->renameColumn('product_option_id', 'option_id');
            $table->renameColumn('additional_price', 'price');
            $table->unsignedBigInteger('product_id')->nullable()->after('option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('option_items', function (Blueprint $table) {
            $table->renameColumn('option_id', 'product_option_id');
            $table->renameColumn('price', 'additional_price');
            $table->dropColumn('product_id');
        });
    }
};
