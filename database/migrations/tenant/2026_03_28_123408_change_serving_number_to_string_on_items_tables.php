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
            $table->string('serving_number')->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('serving_number')->nullable()->change();
        });

        Schema::table('void_items', function (Blueprint $table) {
            $table->string('serving_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedInteger('serving_number')->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('serving_number')->nullable()->change();
        });

        Schema::table('void_items', function (Blueprint $table) {
            $table->integer('serving_number')->nullable()->change();
        });
    }
};
