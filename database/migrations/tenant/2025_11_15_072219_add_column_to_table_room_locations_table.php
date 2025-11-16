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
        Schema::table('table_room_locations', function (Blueprint $table) {
            $table->enum('location_type', ['dine-in', 'takeout'])->default('dine-in')->after('service_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_room_locations', function (Blueprint $table) {
            $table->dropColumn('location_type');
        });
    }
};
