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
        Schema::table('payment_methods', function (Blueprint $table) {
            // Add currency fields to payment_methods
            $table->string('currency_code', 10)->nullable()->after('name');
            $table->string('currency_name', 100)->nullable()->after('currency_code');
            $table->string('symbol', 10)->nullable()->after('currency_name');
            $table->decimal('exchange_rate', 12, 4)->default(1.0000)->after('symbol');
            $table->boolean('is_default_cash')->default(false)->after('exchange_rate')
                ->comment('Marks the default cash payment method');

            // Drop the old currency_id foreign key
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });

        // Add Points payment type (this will be added to the enum in the code)
        // The PaymentType enum will need to be updated separately
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'currency_code',
                'currency_name',
                'symbol',
                'exchange_rate',
                'is_default_cash',
            ]);

            // Re-add currency_id
            $table->foreignId('currency_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
