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
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('seo_title')->nullable()->after('status');
            $table->string('seo_description', 500)->nullable()->after('seo_title');
            $table->string('seo_keywords', 500)->nullable()->after('seo_description');
            $table->string('seo_robots', 50)->default('index,follow')->after('seo_keywords');
            $table->string('seo_canonical_url')->nullable()->after('seo_robots');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->string('seo_title')->nullable()->after('published_at');
            $table->string('seo_description', 500)->nullable()->after('seo_title');
            $table->string('seo_keywords', 500)->nullable()->after('seo_description');
            $table->string('seo_robots', 50)->default('index,follow')->after('seo_keywords');
            $table->string('seo_canonical_url')->nullable()->after('seo_robots');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn([
                'seo_title',
                'seo_description',
                'seo_keywords',
                'seo_robots',
                'seo_canonical_url',
            ]);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn([
                'seo_title',
                'seo_description',
                'seo_keywords',
                'seo_robots',
                'seo_canonical_url',
            ]);
        });
    }
};
