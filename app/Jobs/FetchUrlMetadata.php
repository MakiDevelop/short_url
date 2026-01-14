<?php

namespace App\Jobs;

use App\Models\UrlShortener;
use App\Services\HtmlParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchUrlMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * The URL shortener model instance.
     *
     * @var \App\Models\UrlShortener
     */
    protected $urlShortener;

    /**
     * The original URL to fetch metadata from.
     *
     * @var string
     */
    protected $originalUrl;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\UrlShortener  $urlShortener
     * @param  string  $originalUrl
     * @return void
     */
    public function __construct(UrlShortener $urlShortener, string $originalUrl)
    {
        $this->urlShortener = $urlShortener;
        $this->originalUrl = $originalUrl;
    }

    /**
     * Execute the job.
     *
     * @param  \App\Services\HtmlParserService  $htmlService
     * @return void
     */
    public function handle(HtmlParserService $htmlService)
    {
        try {
            $metaProperties = config('common.metaProperty');
            $metaData = $htmlService->metaData(
                $this->originalUrl,
                $metaProperties,
                $this->urlShortener->short_url
            );

            // Only update if we got valid metadata
            if (!empty($metaData['og_title']) || !empty($metaData['og_description'])) {
                $this->urlShortener->update([
                    'content_type'   => $metaData['content_type'] ?? null,
                    'og_title'       => $metaData['og_title'] ?? null,
                    'og_description' => $metaData['og_description'] ?? null,
                    'og_image'       => $metaData['og_image'] ?? null,
                ]);

                Log::info('Metadata fetched successfully', [
                    'short_url' => $this->urlShortener->short_url,
                    'title' => $metaData['og_title'] ?? 'N/A',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch metadata', [
                'short_url' => $this->urlShortener->short_url,
                'url' => $this->originalUrl,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Metadata fetch job failed permanently', [
            'short_url' => $this->urlShortener->short_url,
            'url' => $this->originalUrl,
            'error' => $exception->getMessage(),
        ]);
    }
}
