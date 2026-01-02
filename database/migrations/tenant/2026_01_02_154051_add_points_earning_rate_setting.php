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
        // Add points_earning_rate setting to general settings group
        DB::table('settings')->insert([
            'group' => 'general',
            'name' => 'points_earning_rate',
            'locked' => false,
            'payload' => json_encode(100.0),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'general')
            ->where('name', 'points_earning_rate')
            ->delete();
    }
};
