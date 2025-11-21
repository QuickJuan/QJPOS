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
        Schema::table('branches', function (Blueprint $table) {
            $table->integer('or_number')->nullable()->after('registration_number')->default(1);
            $table->integer('bill_no')->nullable()->after('or_number')->default(1);
            $table->integer('order_number')->nullable()->after('bill_no')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['or_number', 'bill_no', 'order_number']);
        });
    }
};
