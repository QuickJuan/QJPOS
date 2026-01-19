<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url')->nullable();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('target')->default('_self'); // _self, _blank
            $table->integer('order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
