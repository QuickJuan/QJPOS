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
        Schema::table('cashier_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('cashier_sessions', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('cashier_id');

                $table->foreign('branch_id')
                    ->references('id')
                    ->on('branches')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('cashier_sessions', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }
};
