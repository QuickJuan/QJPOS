<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_feedbacks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedInteger('invoice_no')->index();

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('message')->nullable();

            $table->string('status')->default('pending')->index();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_feedbacks');
    }
};

