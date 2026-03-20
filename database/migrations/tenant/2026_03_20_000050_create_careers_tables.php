<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'internship'])->default('full-time');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('responsibilities')->nullable();
            $table->string('salary_range')->nullable();
            $table->enum('status', ['available', 'closed', 'draft'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('career_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('resume_path');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['new', 'reviewing', 'shortlisted', 'rejected'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_applications');
        Schema::dropIfExists('careers');
    }
};
