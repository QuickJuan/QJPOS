<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashier_cashouts', function (Blueprint $table) {
            $table->string('type')->default('cash_out')->after('cashier_session_id');
            $table->string('source_name')->nullable()->after('type');
        });

        DB::table('cashier_cashouts')
            ->whereNull('type')
            ->update(['type' => 'cash_out']);

        DB::statement('UPDATE cashier_cashouts SET source_name = vendor_name WHERE source_name IS NULL');

        Schema::table('cashier_cashouts', function (Blueprint $table) {
            $table->dropColumn('vendor_name');
        });
    }

    public function down(): void
    {
        Schema::table('cashier_cashouts', function (Blueprint $table) {
            $table->string('vendor_name')->nullable()->after('amount');
        });

        DB::statement('UPDATE cashier_cashouts SET vendor_name = source_name WHERE vendor_name IS NULL');

        Schema::table('cashier_cashouts', function (Blueprint $table) {
            $table->dropColumn(['source_name', 'type']);
        });
    }
};
