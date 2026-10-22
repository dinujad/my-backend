<?php

namespace Tests\Feature;

use App\Models\SeoSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeoSettingApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite') {
            $this->fail('SEO API tests must use sqlite, not '.config('database.default'));
        }

        Cache::flush();
        Schema::dropIfExists('seo_settings');
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('seo_settings');
        parent::tearDown();
    }

    public function test_public_endpoints_allowlist_site_fields_and_disable_caching(): void
    {
        SeoSetting::set('site_title', 'Print Works Custom Title');
        SeoSetting::set('site_description', 'A complete site description that the API must return in full.');
        SeoSetting::set('google_analytics_id', 'G-SECRET');
        SeoSetting::set('google_search_console', 'verify-secret');
        SeoSetting::set('site_keywords', 'secret keywords');
        SeoSetting::set('robots_txt', 'User-agent: *');
        SeoSetting::set('canonical_base_url', 'https://printworks.lk');
        SeoSetting::set('twitter_handle', '@secret');
        SeoSetting::set('facebook_page', 'https://facebook.com/secret');
        SeoSetting::set('og_image', '/secret.png');

        foreach (['/api/v1/seo-settings', '/api/seo-settings'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $cacheControl = (string) $response->headers->get('Cache-Control');
            $this->assertStringContainsString('no-store', $cacheControl);

            $payload = $response->json();
            $this->assertSame(['site_description', 'site_title'], collect(array_keys($payload))->sort()->values()->all());
            $this->assertSame('Print Works Custom Title', $payload['site_title']);
            $this->assertSame('A complete site description that the API must return in full.', $payload['site_description']);

            $body = $response->getContent();
            $this->assertStringNotContainsString('G-SECRET', $body);
            $this->assertStringNotContainsString('verify-secret', $body);
            $this->assertStringNotContainsString('secret keywords', $body);
            $this->assertStringNotContainsString('@secret', $body);
        }
    }

    public function test_saved_settings_are_returned_fresh_after_update(): void
    {
        SeoSetting::set('site_title', 'Old Title');
        SeoSetting::set('site_description', 'Old Description');

        $this->getJson('/api/v1/seo-settings')
            ->assertOk()
            ->assertJsonPath('site_title', 'Old Title')
            ->assertJsonPath('site_description', 'Old Description');

        SeoSetting::set('site_title', 'New Title');
        SeoSetting::set('site_description', 'New Description');

        $this->getJson('/api/v1/seo-settings')
            ->assertOk()
            ->assertJsonPath('site_title', 'New Title')
            ->assertJsonPath('site_description', 'New Description');
    }

    public function test_missing_settings_are_null_rather_than_other_rows(): void
    {
        SeoSetting::set('google_analytics_id', 'G-SECRET');

        $this->getJson('/api/seo-settings')
            ->assertOk()
            ->assertExactJson([
                'site_title' => null,
                'site_description' => null,
            ]);
    }
}
