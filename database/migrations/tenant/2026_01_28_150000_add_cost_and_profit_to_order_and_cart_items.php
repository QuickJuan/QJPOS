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
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'cost')) {
                $table->decimal('cost', 15, 2)->default(0)->after('amount');
            }

            if (! Schema::hasColumn('order_items', 'profit')) {
                $table->decimal('profit', 15, 2)->default(0)->after('cost');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'cost')) {
                $table->decimal('cost', 15, 2)->default(0)->after('amount');
            }

            if (! Schema::hasColumn('cart_items', 'profit')) {
                $table->decimal('profit', 15, 2)->default(0)->after('cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'profit')) {
                $table->dropColumn('profit');
            }

            if (Schema::hasColumn('order_items', 'cost')) {
                $table->dropColumn('cost');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'profit')) {
                $table->dropColumn('profit');
            }

            if (Schema::hasColumn('cart_items', 'cost')) {
                $table->dropColumn('cost');
            }
        });
    }
};
