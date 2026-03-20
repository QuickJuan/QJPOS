<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the Spatie LaravelSettings table in the central database.
 * Tenant databases get this table via database/migrations/tenant/.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();
            $table->unique(['group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
