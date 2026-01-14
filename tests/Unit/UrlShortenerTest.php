<?php

namespace Tests\Unit;

use App\Repositories\UrlShortenerRepository;
use App\Models\UrlShortener;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    protected $urlRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlRepository = app(UrlShortenerRepository::class);
    }

    public function testGenerateCodeReturnsEightCharacters()
    {
        $code = $this->urlRepository->generateCode();

        $this->assertEquals(8, strlen($code));
    }

    public function testGenerateCodeReturnsUniqueCode()
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = $this->urlRepository->generateCode();
        }

        $this->assertEquals(count($codes), count(array_unique($codes)));
    }

    public function testInsertCreatesUrlRecord()
    {
        $data = [
            'lu_id' => 1,
            'original_url' => 'https://example.com',
            'short_url' => 'abc12345',
            'og_title' => 'Test Title',
            'og_description' => 'Test Description',
            'ip' => '127.0.0.1',
        ];

        $url = $this->urlRepository->insert($data);

        $this->assertInstanceOf(UrlShortener::class, $url);
        $this->assertEquals('https://example.com', $url->original_url);
        $this->assertEquals('abc12345', $url->short_url);
    }

    public function testGetByCodeReturnsCorrectUrl()
    {
        $data = [
            'lu_id' => 1,
            'original_url' => 'https://example.com',
            'short_url' => 'test1234',
            'ip' => '127.0.0.1',
        ];

        $this->urlRepository->insert($data);
        $found = $this->urlRepository->getByCode('test1234');

        $this->assertNotNull($found);
        $this->assertEquals('https://example.com', $found->original_url);
    }

    public function testGetByCodeReturnsNullForNonExistent()
    {
        $found = $this->urlRepository->getByCode('nonexist');

        $this->assertNull($found);
    }
}
