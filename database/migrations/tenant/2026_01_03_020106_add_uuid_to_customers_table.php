<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->after('id');
        });

        // Generate UUIDs for existing customers
        DB::table('customers')->whereNull('uuid')->get()->each(function ($customer) {
            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        // Make UUID required after populating existing records
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
