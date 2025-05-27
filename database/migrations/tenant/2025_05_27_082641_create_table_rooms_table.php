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
        Schema::create('table_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('chairs')->default(1);
            $table->boolean('with_timeframe')->default(false);
            $table->unsignedInteger('merge_to')->nullable()->comments('ID of the room to merge with, if applicable');
            $table->string('status', 15)->default('Vacant')->comments('Status of the room, e.g., vacant, occupied, reserved');
            $table->datetime('time_in')->nullable()->comments('Time when the room was occupied or reserved');
            $table->datetime('time_out')->nullable()->comments('Time when the room will be vacated or reserved until');
            $table->integer('limit_hours')->nullable()->comments('Maximum hours allowed for the room usage');
            $table->integer('table_width')->default(0)->comments('Width of the tables in the room, if applicable');
            $table->integer('table_height')->default(0)->comments('Height of the tables in the room, if applicable');
            $table->integer('table_x')->default(0)->comments('X coordinate for table placement in the room layout');
            $table->integer('table_y')->default(0)->comments('Y coordinate for table placement in the room layout');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_rooms');
    }
};
