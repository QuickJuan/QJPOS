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
        Schema::table('table_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('table_room_location_id')->nullable()->after('branch_id');
            $table->integer('pax_limit')->nullable()->after('table_room_location_id');
            $table->string('screen_position')->nullable()->after('pax_limit');
            $table->datetime('dining_start')->nullable()->after('screen_position');
            $table->datetime('dining_end')->nullable()->after('dining_start');
            $table->longText('notes')->nullable()->after('dining_end');
            $table->string('featured_image')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_rooms', function (Blueprint $table) {
            $table->dropColumn([
                'table_room_location_id',
                'pax_limit',
                'screen_position',
                'dining_start',
                'dining_end',
                'notes',
                'featured_image',
            ]);
        });
    }
};
