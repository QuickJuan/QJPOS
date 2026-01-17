<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_add_ons') || ! Schema::hasColumn('product_add_ons', 'add_on_price')) {
            return;
        }

        // Avoid doctrine/dbal requirement by using a raw ALTER.
        // MySQL/MariaDB syntax.
        DB::statement("ALTER TABLE product_add_ons MODIFY add_on_price DECIMAL(12,2) NOT NULL DEFAULT 0");
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_add_ons') || ! Schema::hasColumn('product_add_ons', 'add_on_price')) {
            return;
        }

        DB::statement("ALTER TABLE product_add_ons MODIFY add_on_price INT NOT NULL DEFAULT 0");
    }
};
