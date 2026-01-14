<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\LoginUser;
use App\Models\UrlShortener;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UrlShortenerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testHomepageLoads()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testLoginPageLoads()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function testShortUrlCreationRequiresValidUrl()
    {
        $response = $this->postJson('/index/short_url', [
            'url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    public function testShortUrlCreationWithValidUrl()
    {
        $response = $this->postJson('/index/short_url', [
            'url' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'code', 'short_url']);
    }

    public function testShortUrlRedirect()
    {
        UrlShortener::create([
            'lu_id' => 0,
            'original_url' => 'https://example.com',
            'short_url' => 'testcode',
            'og_title' => 'Test',
            'ip' => '127.0.0.1',
            'clicks' => 0,
        ]);

        $response = $this->get('/testcode');

        $response->assertStatus(200);
    }

    public function testNonExistentShortUrlRedirectsHome()
    {
        $response = $this->get('/nonexistent');

        $response->assertRedirect('/');
    }

    public function testPrivacyPolicyPageLoads()
    {
        $response = $this->get('/policies/privacy');

        $response->assertStatus(200);
    }

    public function testTermsPageLoads()
    {
        $response = $this->get('/policies/terms');

        $response->assertStatus(200);
    }

    public function testRateLimitingOnShortUrlCreation()
    {
        for ($i = 0; $i < 31; $i++) {
            $response = $this->postJson('/index/short_url', [
                'url' => 'https://example.com',
            ]);
        }

        $response->assertStatus(429);
    }

    public function testUrlValidationRejectsLongUrls()
    {
        $longUrl = 'https://example.com/' . str_repeat('a', 2100);

        $response = $this->postJson('/index/short_url', [
            'url' => $longUrl,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }
}
