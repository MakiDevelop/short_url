<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\LoginUser;
use App\Models\UrlShortener;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $apiToken = 'test-api-token-12345678901234567890123456789012345678901234567890';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = LoginUser::create([
            'oauth_type' => 'google',
            'oauth_id' => '12345',
            'oauth_name' => 'Test User',
            'oauth_email' => 'test@example.com',
            'oauth_first_time' => now(),
            'api_token' => $this->apiToken,
        ]);
    }

    public function testCreateShortUrlWithoutAuth()
    {
        $response = $this->postJson('/api/v1/urls', [
            'url' => 'https://example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['code', 'short_url', 'original_url'],
            ]);
    }

    public function testCreateShortUrlWithAuth()
    {
        $response = $this->postJson('/api/v1/urls', [
            'url' => 'https://example.com',
            'title' => 'Test Title',
        ], [
            'Authorization' => 'Bearer ' . $this->apiToken,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    public function testCreateShortUrlWithInvalidUrl()
    {
        $response = $this->postJson('/api/v1/urls', [
            'url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function testCreateShortUrlWithCustomCode()
    {
        $response = $this->postJson('/api/v1/urls', [
            'url' => 'https://example.com',
            'custom_code' => 'mycode123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => 'mycode123',
                ],
            ]);
    }

    public function testCreateShortUrlWithDuplicateCustomCode()
    {
        UrlShortener::create([
            'lu_id' => 0,
            'original_url' => 'https://example.com',
            'short_url' => 'existing',
            'ip' => '127.0.0.1',
        ]);

        $response = $this->postJson('/api/v1/urls', [
            'url' => 'https://example.com',
            'custom_code' => 'existing',
        ]);

        $response->assertStatus(409)
            ->assertJson(['success' => false]);
    }

    public function testGetUrlInfo()
    {
        UrlShortener::create([
            'lu_id' => 0,
            'original_url' => 'https://example.com',
            'short_url' => 'testinfo',
            'og_title' => 'Test Title',
            'ip' => '127.0.0.1',
            'clicks' => 5,
        ]);

        $response = $this->getJson('/api/v1/urls/testinfo');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => 'testinfo',
                    'original_url' => 'https://example.com',
                    'clicks' => 5,
                ],
            ]);
    }

    public function testGetNonExistentUrl()
    {
        $response = $this->getJson('/api/v1/urls/nonexistent');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function testListUrlsRequiresAuth()
    {
        $response = $this->getJson('/api/v1/urls');

        $response->assertStatus(401);
    }

    public function testListUrlsWithAuth()
    {
        UrlShortener::create([
            'lu_id' => $this->user->id,
            'original_url' => 'https://example.com',
            'short_url' => 'userurl1',
            'ip' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/urls', [
            'Authorization' => 'Bearer ' . $this->apiToken,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data',
                'pagination' => ['current_page', 'total'],
            ]);
    }

    public function testDeleteUrlRequiresAuth()
    {
        $response = $this->deleteJson('/api/v1/urls/somecode');

        $response->assertStatus(401);
    }

    public function testDeleteOwnUrl()
    {
        UrlShortener::create([
            'lu_id' => $this->user->id,
            'original_url' => 'https://example.com',
            'short_url' => 'todelete',
            'ip' => '127.0.0.1',
        ]);

        $response = $this->deleteJson('/api/v1/urls/todelete', [], [
            'Authorization' => 'Bearer ' . $this->apiToken,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNull(UrlShortener::where('short_url', 'todelete')->first());
    }

    public function testGetUserInfo()
    {
        $response = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer ' . $this->apiToken,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);
    }
}
