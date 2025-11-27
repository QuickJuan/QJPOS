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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->date('birth_date')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();
            $table->enum('type', ['regular', 'vip'])->default('regular');
            $table->timestamp('last_visit')->nullable();
            $table->boolean('email_subscribe')->default(false);
            $table->boolean('sms_subscribe')->default(false);
            $table->timestamps();

            $table->index('customer_name');
            $table->index('contact_no');
            $table->index('email');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
