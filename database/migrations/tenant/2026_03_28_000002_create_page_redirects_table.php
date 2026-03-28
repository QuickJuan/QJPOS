<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique()->comment('Path to redirect FROM, e.g. /old-about or /blog/old-post');
            $table->string('to_url')->comment('Destination URL or path, e.g. /about or https://example.com/new');
            $table->smallInteger('redirect_type')->default(301)->comment('HTTP redirect status: 301 (permanent) or 302 (temporary)');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable()->comment('Internal notes about why this redirect exists');
            $table->timestamps();

            $table->index(['from_path', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_redirects');
    }
};
