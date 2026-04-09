<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printer_configs', function (Blueprint $table) {
            $table->foreignId('cashier_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->index(['cashier_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('printer_configs', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropIndex(['cashier_id', 'type', 'is_active']);
            $table->dropColumn('cashier_id');
        });
    }
};

