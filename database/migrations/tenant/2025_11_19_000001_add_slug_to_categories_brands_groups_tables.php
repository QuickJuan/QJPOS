<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add slug column without unique constraint first (only if it doesn't exist)
        if (!Schema::hasColumn('categories', 'slug')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('brands', 'slug')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('groups', 'slug')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Generate slugs for existing records
        DB::table('categories')->whereNull('slug')->orWhere('slug', '')->get()->each(function ($category) {
            $slug = Str::slug($category->name);
            $count = 1;
            $originalSlug = $slug;

            // Ensure unique slug
            while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            DB::table('categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);
        });

        DB::table('brands')->whereNull('slug')->orWhere('slug', '')->get()->each(function ($brand) {
            $slug = Str::slug($brand->name);
            $count = 1;
            $originalSlug = $slug;

            // Ensure unique slug
            while (DB::table('brands')->where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            DB::table('brands')
                ->where('id', $brand->id)
                ->update(['slug' => $slug]);
        });

        DB::table('groups')->whereNull('slug')->orWhere('slug', '')->get()->each(function ($group) {
            $slug = Str::slug($group->name);
            $count = 1;
            $originalSlug = $slug;

            // Ensure unique slug
            while (DB::table('groups')->where('slug', $slug)->where('id', '!=', $group->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            DB::table('groups')
                ->where('id', $group->id)
                ->update(['slug' => $slug]);
        });

        // Now add unique constraint and make not nullable
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
