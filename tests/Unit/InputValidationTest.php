<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class InputValidationTest extends TestCase
{
    protected $rules;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rules = [
            'url' => 'required|url|max:2048',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
            'hash_tag' => 'nullable|string|max:200|regex:/^[\p{L}\p{N},\s\-_]*$/u',
            'ga_id' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9\-]*$/',
            'pixel_id' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9\-]*$/',
        ];
    }

    public function testValidUrlPasses()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com'],
            $this->rules
        );

        $this->assertTrue($validator->passes());
    }

    public function testInvalidUrlFails()
    {
        $validator = Validator::make(
            ['url' => 'not-a-url'],
            $this->rules
        );

        $this->assertTrue($validator->fails());
    }

    public function testUrlTooLongFails()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com/' . str_repeat('a', 2100)],
            $this->rules
        );

        $this->assertTrue($validator->fails());
    }

    public function testValidHashtagPasses()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com', 'hash_tag' => 'tag1, tag2, tag-3'],
            $this->rules
        );

        $this->assertTrue($validator->passes());
    }

    public function testHashtagWithSpecialCharsFails()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com', 'hash_tag' => 'tag<script>'],
            $this->rules
        );

        $this->assertTrue($validator->fails());
    }

    public function testValidGaIdPasses()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com', 'ga_id' => 'UA-12345-1'],
            $this->rules
        );

        $this->assertTrue($validator->passes());
    }

    public function testInvalidGaIdFails()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com', 'ga_id' => 'invalid<>id'],
            $this->rules
        );

        $this->assertTrue($validator->fails());
    }

    public function testTitleTooLongFails()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com', 'title' => str_repeat('a', 250)],
            $this->rules
        );

        $this->assertTrue($validator->fails());
    }

    public function testDescriptionTooLongFails()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com', 'description' => str_repeat('a', 550)],
            $this->rules
        );

        $this->assertTrue($validator->fails());
    }

    public function testChineseHashtagPasses()
    {
        $validator = Validator::make(
            ['url' => 'https://example.com', 'hash_tag' => '標籤一, 標籤二'],
            $this->rules
        );

        $this->assertTrue($validator->passes());
    }
}
