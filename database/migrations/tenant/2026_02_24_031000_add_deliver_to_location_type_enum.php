<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `table_room_locations` MODIFY `location_type` ENUM('dine-in','takeout','deliver') NOT NULL DEFAULT 'dine-in'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `table_room_locations` MODIFY `location_type` ENUM('dine-in','takeout') NOT NULL DEFAULT 'dine-in'"
        );
    }
};
