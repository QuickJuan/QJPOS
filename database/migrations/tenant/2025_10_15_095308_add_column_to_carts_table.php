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
        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'cashier_id');
            $table->renameColumn('cashier_shift_id', 'cashier_session_id');
            $table->string('session_id')->nullable()->after('cashier_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('cashier_id', 'customer_id');
            $table->renameColumn('cashier_session_id', 'cashier_shift_id');
            $table->dropColumn('session_id');
        });
    }
};
