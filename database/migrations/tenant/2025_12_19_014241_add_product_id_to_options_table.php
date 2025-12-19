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
        Schema::table('options', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('option_name')->constrained()->onDelete('cascade');
            $table->integer('max_quantity')->nullable()->after('product_id');
            $table->boolean('is_default')->default(false)->after('max_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'max_quantity', 'is_default']);
        });
    }
};
