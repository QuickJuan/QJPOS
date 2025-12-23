<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('unit_measure_id')
                ->nullable()
                ->after('unit_measure')
                ->constrained('unit_measures')
                ->nullOnDelete();
        });

        Schema::create('inventory_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')
                ->constrained('inventories')
                ->cascadeOnDelete();
            $table->foreignId('unit_measure_id')
                ->constrained('unit_measures')
                ->cascadeOnDelete();
            $table->decimal('conversion_factor', 10, 2)->default(1);
            $table->timestamps();
            $table->unique(['inventory_id', 'unit_measure_id']);
        });

        $now = now();
        $existingUnitNames = DB::table('inventories')
            ->select('unit_measure')
            ->whereNotNull('unit_measure')
            ->distinct()
            ->pluck('unit_measure');

        foreach ($existingUnitNames as $name) {
            $name = trim((string) $name);

            if ($name === '') {
                continue;
            }

            $measure = DB::table('unit_measures')->where('name', $name)->first();

            if (! $measure) {
                $id = DB::table('unit_measures')->insertGetId([
                    'name'       => $name,
                    'symbol'     => Str::upper(Str::substr($name, 0, 5)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $id = $measure->id;
            }

            DB::table('inventories')
                ->where('unit_measure', $name)
                ->update(['unit_measure_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_unit_conversions');

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_measure_id');
        });
    }
};
