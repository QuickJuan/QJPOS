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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->after('id')
                ->constrained('branches')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->comment('The branch where the attendance was recorded');

            $table->foreignId('user_id')
                ->after('branch_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->comment('The user who clocked in or out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
