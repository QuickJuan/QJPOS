<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_cashouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cashier_session_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('vendor_name')->nullable();
            $table->string('purpose')->nullable();
            $table->text('details');
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['cashier_id', 'cashier_session_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_cashouts');
    }
};
