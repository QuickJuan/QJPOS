<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('source')->default('staff')->after('branch_id');
            $table->string('reference_no')->nullable()->after('source');
            $table->timestamp('submitted_at')->nullable()->after('reference_no');
            $table->timestamp('processed_at')->nullable()->after('submitted_at');

            $table->index('source');
            $table->unique('reference_no');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['reference_no']);
            $table->dropIndex(['source']);
            $table->dropColumn([
                'source',
                'reference_no',
                'submitted_at',
                'processed_at',
            ]);
        });
    }
};
