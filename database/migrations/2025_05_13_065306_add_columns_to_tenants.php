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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('tenancy_db_name')->after('id');
            $table->string('name')->after('tenancy_db_name')->nullable();
            $table->string('email')->after('name');
            $table->string('phone')->after('email')->nullable();
            $table->string('address')->after('phone')->nullable();
            $table->string('city')->after('address')->nullable();
            $table->string('state')->after('city')->nullable();
            $table->string('country')->after('state')->nullable();
            $table->string('subscription')->after('country')->nullable()->comments('subcription type');
            $table->string('billing_type')->after('subscription')->nullable()->comments('billing type');
            $table->string('subscription_status')->after('billing_type')->nullable()->comments('subcription status');
            $table->datetime('subscription_ends_at')->after('subscription_status')->nullable()->comments('subcription ends at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('tenancy_db_name');
            $table->dropColumn('name');
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('address');
            $table->dropColumn('city');             
            $table->dropColumn('state');
            $table->dropColumn('country');
            $table->dropColumn('subscription');
            $table->dropColumn('billing_type');
            $table->dropColumn('subscription_status');
            $table->dropColumn('subscription_ends_at');

        });
    }
};
