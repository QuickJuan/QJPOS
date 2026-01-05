<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('currency_denominations', function (Blueprint $table) {
            // Check if currency_id column exists
            if (Schema::hasColumn('currency_denominations', 'currency_id')) {
                // Drop old foreign key if it exists
                try {
                    $table->dropForeign(['currency_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }

                // Drop unique constraint if it exists
                try {
                    $table->dropUnique(['currency_id', 'value']);
                } catch (\Exception $e) {
                    // Unique constraint might not exist
                }

                $table->dropColumn('currency_id');
            }

            // Add new foreign key to payment_methods if not already exists
            if (!Schema::hasColumn('currency_denominations', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->after('id')->constrained('payment_methods')->cascadeOnDelete();

                // Add new unique constraint
                $table->unique(['payment_method_id', 'value']);
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_denominations', function (Blueprint $table) {
            // Drop payment_method foreign key and unique constraint
            $table->dropForeign(['payment_method_id']);
            $table->dropUnique(['payment_method_id', 'value']);

            // Restore currency_id
            $table->dropColumn('payment_method_id');
            $table->foreignId('currency_id')->after('id')->constrained('currencies')->cascadeOnDelete();

            // Restore old unique constraint
            $table->unique(['currency_id', 'value']);
        });
    }
};
