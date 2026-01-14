<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ClickLogRepository;
use App\Repositories\UrlShortenerRepository;
use App\Services\HtmlParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UrlController extends Controller
{
    protected $urlRepository;
    protected $logRepository;
    protected $htmlService;

    public function __construct(
        UrlShortenerRepository $urlRepository,
        ClickLogRepository $logRepository,
        HtmlParserService $htmlService
    ) {
        $this->urlRepository = $urlRepository;
        $this->logRepository = $logRepository;
        $this->htmlService = $htmlService;
    }

    /**
     * Create a short URL
     * POST /api/v1/urls
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
            'custom_code' => 'nullable|string|alpha_num|min:4|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customCode = $request->input('custom_code');

            if ($customCode) {
                $existing = $this->urlRepository->getByCode($customCode);
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Custom code already exists',
                    ], 409);
                }
                $code = $customCode;
            } else {
                $code = $this->urlRepository->generateCode();
            }

            $metaDatas = [
                'content_type' => 'text/html',
                'og_title' => $request->input('title', ''),
                'og_description' => $request->input('description', ''),
                'og_image' => '',
            ];

            if (empty($metaDatas['og_title'])) {
                $metaDatas = $this->htmlService->metaData(
                    $request->input('url'),
                    config('common.metaProperty'),
                    $code
                );
            }

            $insertData = [
                'lu_id' => $request->user()->id ?? 0,
                'original_url' => $request->input('url'),
                'short_url' => $code,
                'ip' => $request->ip(),
                'content_type' => $metaDatas['content_type'] ?? 'text/html',
                'og_title' => $metaDatas['og_title'] ?? '',
                'og_description' => $metaDatas['og_description'] ?? '',
                'og_image' => $metaDatas['og_image'] ?? '',
            ];

            $urlData = $this->urlRepository->insert($insertData);

            return response()->json([
                'success' => true,
                'data' => [
                    'code' => $code,
                    'short_url' => url($code),
                    'original_url' => $urlData->original_url,
                    'title' => $urlData->og_title,
                    'created_at' => $urlData->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('API: URL creation failed', [
                'url' => $request->input('url'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create short URL',
            ], 500);
        }
    }

    /**
     * Get URL info
     * GET /api/v1/urls/{code}
     */
    public function show($code)
    {
        $url = $this->urlRepository->getByCode($code);

        if (!$url) {
            return response()->json([
                'success' => false,
                'error' => 'URL not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $url->short_url,
                'short_url' => url($url->short_url),
                'original_url' => $url->original_url,
                'title' => $url->og_title,
                'description' => $url->og_description,
                'image' => $url->og_image,
                'clicks' => $url->clicks,
                'created_at' => $url->created_at,
            ],
        ]);
    }

    /**
     * Get URL analytics
     * GET /api/v1/urls/{code}/analytics
     */
    public function analytics($code)
    {
        $url = $this->urlRepository->getByCode($code);

        if (!$url) {
            return response()->json([
                'success' => false,
                'error' => 'URL not found',
            ], 404);
        }

        $referralData = $this->logRepository->analyticsReferral($code);
        $osData = $this->logRepository->analyticsOs($code);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $code,
                'total_clicks' => $url->clicks,
                'referrals' => $referralData,
                'operating_systems' => $osData,
            ],
        ]);
    }

    /**
     * Delete URL
     * DELETE /api/v1/urls/{code}
     */
    public function destroy(Request $request, $code)
    {
        $userId = $request->user()->id ?? 0;
        $url = $this->urlRepository->getByUserCode($userId, $code);

        if (!$url) {
            return response()->json([
                'success' => false,
                'error' => 'URL not found or unauthorized',
            ], 404);
        }

        $this->urlRepository->delete($url);

        return response()->json([
            'success' => true,
            'message' => 'URL deleted successfully',
        ]);
    }

    /**
     * List user's URLs
     * GET /api/v1/urls
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id ?? 0;

        if (!$userId) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication required',
            ], 401);
        }

        $params = [
            'lu_id' => $userId,
            'keyword' => $request->query('keyword'),
        ];

        $urls = $this->urlRepository->list($params);

        return response()->json([
            'success' => true,
            'data' => $urls->map(function ($url) {
                return [
                    'code' => $url->short_url,
                    'short_url' => url($url->short_url),
                    'original_url' => $url->original_url,
                    'title' => $url->og_title,
                    'clicks' => $url->clicks,
                    'created_at' => $url->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $urls->currentPage(),
                'last_page' => $urls->lastPage(),
                'per_page' => $urls->perPage(),
                'total' => $urls->total(),
            ],
        ]);
    }
}
