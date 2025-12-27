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
        Schema::table('tenant_applications', function (Blueprint $table) {
            $table->boolean('accept_terms')->default(false)->after('notes');
            $table->boolean('accept_privacy')->default(false)->after('accept_terms');
            $table->boolean('accept_promotions')->default(false)->after('accept_privacy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_applications', function (Blueprint $table) {
            $table->dropColumn(['accept_terms', 'accept_privacy', 'accept_promotions']);
        });
    }
};
