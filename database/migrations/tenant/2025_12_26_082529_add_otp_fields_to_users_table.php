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
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp_secret')->nullable()->after('password')->comment('TOTP secret key for time-based OTP');
            $table->boolean('otp_enabled')->default(false)->after('otp_secret')->comment('Whether OTP is enabled for this user');
            $table->timestamp('otp_enabled_at')->nullable()->after('otp_enabled')->comment('When OTP was enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_secret', 'otp_enabled', 'otp_enabled_at']);
        });
    }
};
