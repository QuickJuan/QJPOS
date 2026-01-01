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
        Schema::table('void_items', function (Blueprint $table) {
            $table->string('tax_type')->nullable()->after('sub_total');
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('tax_type');
            $table->boolean('tax_included')->default(false)->after('tax_percentage');
            $table->integer('serving_priority')->nullable()->after('tax_included');
            $table->boolean('is_served')->default(false)->after('serving_priority');
            $table->timestamp('served_at')->nullable()->after('is_served');
            $table->integer('duration_in_seconds')->nullable()->after('served_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('void_items', function (Blueprint $table) {
            $table->dropColumn([
                'tax_type',
                'tax_percentage',
                'tax_included',
                'serving_priority',
                'is_served',
                'served_at',
                'duration_in_seconds',
            ]);
        });
    }
};
