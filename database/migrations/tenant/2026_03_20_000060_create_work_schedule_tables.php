<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shift template definitions (Opening, Graveyard, Mid-shift, etc.)
        Schema::create('schedule_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // e.g. "Opening", "Graveyard"
            $table->time('expected_time_in');               // e.g. "06:00:00"
            $table->unsignedTinyInteger('duration_hours')->default(8); // typically 8
            $table->string('color', 7)->default('#6366f1'); // hex, for badge/calendar
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Per-user per-day schedule assignments
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('schedule_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            // One schedule entry per user per day
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('schedule_types');
    }
};
