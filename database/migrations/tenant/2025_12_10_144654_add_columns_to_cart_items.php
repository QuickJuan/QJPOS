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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('tax_type', 10)->default('vat')->after('price');
            $table->integer('tax_percentage')->default(12)->after('tax_type');
            $table->boolean('tax_included')->default(true)->after('tax_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('tax_type');
            $table->dropColumn('tax_percentage');
            $table->dropColumn('tax_included');
        });
    }
};
