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
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('earned_points', 10, 2)->default(0)->after('sms_subscribe');
            $table->decimal('redeemed_points', 10, 2)->default(0)->after('earned_points');
            $table->decimal('balance', 10, 2)->default(0)->after('redeemed_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['earned_points', 'redeemed_points', 'balance']);
        });
    }
};
