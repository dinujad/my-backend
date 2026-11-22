<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('featured_image_alt', 255)->nullable()->after('featured_image');
            $table->string('focus_keyword', 100)->nullable()->after('seo_description');
            $table->string('meta_keywords', 255)->nullable()->after('focus_keyword');
            $table->string('canonical_url', 500)->nullable()->after('meta_keywords');
            $table->string('og_image', 500)->nullable()->after('canonical_url');
            $table->boolean('noindex')->default(false)->after('og_image');
            $table->json('tags')->nullable()->after('noindex');
        });

        // Google shows ~60 chars of title / ~160 of description; give a little headroom.
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('seo_title', 70)->nullable()->change();
            $table->string('seo_description', 170)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'featured_image_alt', 'focus_keyword', 'meta_keywords',
                'canonical_url', 'og_image', 'noindex', 'tags',
            ]);
        });
    }
};
