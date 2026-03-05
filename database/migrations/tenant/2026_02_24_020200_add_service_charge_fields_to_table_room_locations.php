<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_room_locations', function (Blueprint $table) {
            $table->string('service_charge_label')->nullable()->after('service_charge');
            $table->enum('service_charge_type', ['auto', 'manual'])->default('auto')->after('service_charge_label');
        });
    }

    public function down(): void
    {
        Schema::table('table_room_locations', function (Blueprint $table) {
            $table->dropColumn(['service_charge_type', 'service_charge_label']);
        });
    }
};
