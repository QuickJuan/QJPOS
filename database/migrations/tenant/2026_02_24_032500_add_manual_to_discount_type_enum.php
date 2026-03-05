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
            "ALTER TABLE `discounts` MODIFY `discount_type` ENUM('regular','special','senior','pwd') NOT NULL DEFAULT 'regular'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `discounts` MODIFY `discount_type` ENUM('regular','special','senior','pwd') NOT NULL DEFAULT 'regular'"
        );
    }
};
