<?php

namespace App\Http\Controllers;

use App\Repositories\ClickLogRepository;
use App\Repositories\HashTagsRepository;
use App\Repositories\UrlShortenerRepository;
use App\Services\HtmlParserService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;

class IndexController extends Controller
{
    protected $client;
    protected $htmlService;
    private $urlRepository, $logRepository, $tagsRepository;
    public function __construct(HtmlParserService $htmlService, UrlShortenerRepository $urlRepository, ClickLogRepository $logRepository, HashTagsRepository $tagsRepository)
    {
        $this->client         = new \GuzzleHttp\Client();
        $this->htmlService    = $htmlService;
        $this->urlRepository  = $urlRepository;
        $this->logRepository  = $logRepository;
        $this->tagsRepository = $tagsRepository;
    }

    public function index(Request $request)
    {
        if (Auth::guard('user')->id()) {
            $params          = $querys          = $request->query();
            $params['lu_id'] = Auth::guard('user')->id();
            $lists           = $this->urlRepository->list($params);
            return view('user_index', compact('lists', 'querys'));
        }
        return view('index');
    }

    public function urlData(Request $request, $code)
    {
        $data = $this->urlRepository->getCodeForUpdate($code);
        if ($data) {
            $os          = new Os();
            $browser     = new Browser();
            $referralUrl = $request->server('HTTP_REFERER');
            $referral    = 'Direct';
            // 記錄log
            if ($referralUrl) {
                $referralTypes = config('common.referralTypes');
                foreach ($referralTypes as $type) {
                    if (strpos($referralUrl, strtolower($type)) !== false) {
                        $referral = $type;
                        break;
                    }
                }
            }
            $insertData = [
                'us_id'        => $data->id,
                'short_url'    => $code,
                'referral_url' => $referralUrl,
                'referral'     => $referral,
                'os'           => $os->getName(),
                'browser'      => $browser->getName(),
                'user_agenet'  => $request->header('User-Agent'),
                'ip'           => $request->ip(),
                'click_time'   => date('Y-m-d H:i:s'),
            ];
            $this->logRepository->insert($insertData);
            // update clicks number
            $data->clicks += 1;
            $data->save();
            // 確認是否要轉頁
            $isRedirect = true;
            if (strpos($request->header('User-Agent'), 'facebookexternalhit') !== false) {
                $isRedirect = false;
            }

            return view('url', compact('data', 'isRedirect'));
        }
        return redirect('/');
    }

    public function shortUrl(Request $request)
    {
        $response = [
            'success' => false,
        ];
        $post = $request->post();
        if (isset($post['code']) && Auth::guard('user')->check() && $post['code']) {
            // 修改
            $urlData   = $this->urlRepository->getByUserCode(Auth::guard('user')->id(), $post['code']);
            $validator = $this->setValidate($request, Auth::guard('user')->id(), $urlData->id);
            if ($urlData && $validator->passes()) {
                $updateData = [
                    'original_url'   => $post['url'],
                    'content_type'   => $post['content_type'],
                    'og_title'       => $post['title'],
                    'og_description' => $post['description'],
                    'og_image'       => $post['image'] ?? '',
                    'gacode_id'      => $post['ga_id'] ?? '',
                    'fbpixel_id'     => $post['pixel_id'] ?? '',
                    'utm_source'     => $post['source'] ?? '',
                    'utm_medium'     => $post['medium'] ?? '',
                    'utm_campaign'   => $post['campaign'] ?? '',
                    'utm_term'       => $post['term'] ?? '',
                    'utm_content'    => $post['content'] ?? '',
                    'hashtag'        => $post['hash_tag'] ?? '',
                ];
                if ($request->hasFile('image_file')) {
                    $image = $request->file('image_file');
                    $fileName = $this->handleImageUpload($image);
                    if ($fileName) {
                        $updateData['og_image'] = $fileName;
                        $response['image'] = $fileName;
                    } else {
                        $response['image'] = 'invalid';
                    }
                } else {
                    $response['image'] = 'not have';
                }
                $isUpdate         = $this->urlRepository->update($urlData->id, $updateData);
                if ($isUpdate) {
                    // hash tag
                    $tags = explode(',', $post['hash_tag']);
                    if (count($tags)) {
                        $this->tagsRepository->processTags($urlData->id, $tags);
                    }
                    $response['success'] = true;
                    $response['msg']     = '修改成功';
                }
            } else {
                if (empty($urlData)) {
                    $response['msg'] = '請確認資料是否正確!';
                } else {
                    $response['msg'] = implode('<br />', $validator->errors()->all());
                }
            }
        } else if (empty($post['code']) && isset($post['url'])) {
            // 新增
            $validator = $this->setValidate($request, Auth::guard('user')->id());
            if ($validator->passes()) {
                $code = $this->urlRepository->generateCode();

                $tmpData = [
                    'lu_id'        => Auth::guard('user')->id() ?? 0,
                    'original_url' => $post['url'],
                    'short_url'    => $code,
                    'hashtag'      => $post['hash_tag'] ?? '',
                    'ip'           => $request->ip(),
                ];
                if (Auth::guard('user')->check()) {
                    $metaDatas = [
                        'content_type'   => $post['content_type'],
                        'og_title'       => $post['title'],
                        'og_description' => $post['description'],
                        'og_image'       => $post['image'] ?? '',
                        'gacode_id'      => $post['ga_id'] ?? '',
                        'fbpixel_id'     => $post['pixel_id'] ?? '',
                        'utm_source'     => $post['source'] ?? '',
                        'utm_medium'     => $post['medium'] ?? '',
                        'utm_campaign'   => $post['campaign'] ?? '',
                        'utm_term'       => $post['term'] ?? '',
                        'utm_content'    => $post['content'] ?? '',
                    ];
                    if ($request->hasFile('image_file')) {
                        $image = $request->file('image_file');
                        $fileName = $this->handleImageUpload($image);
                        if ($fileName) {
                            $metaDatas['og_image'] = $fileName;
                        }
                    }
                } else {
                    $metaDatas = $this->htmlService->metaData($post['url'], config('common.metaProperty'), $code);
                }

                try {
                    $insertData = array_merge($tmpData, $metaDatas);
                    $urlData    = $this->urlRepository->insert($insertData);
                    // hash tag
                    if (Auth::guard('user')->check()) {
                        $tags = explode(',', $post['hash_tag']);
                        if (count($tags)) {
                            $this->tagsRepository->processTags($urlData->id, $tags);
                        }
                    }
                    $response['success']   = true;
                    $response['code']      = $code;
                    $response['short_url'] = url($code);
                } catch (\Exception $e) {
                    Log::error('URL creation failed', [
                        'url' => $post['url'] ?? null,
                        'user_id' => Auth::guard('user')->id(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $response['err'] = true;
                    $response['msg'] = '資料錯誤請聯絡管理員';
                }
            } else {
                $response['msg'] = implode('<br />', $validator->errors()->all());
            }
        }
        return response()->json($response);
    }

    public function website(Request $request)
    {
        $response = [
            'success' => false,
            'data'    => [],
            'msg'     => '',
        ];
        if (Auth::guard('user')->check()) {
            $post = $request->post();
            if ($post && isset($post['url'])) {
                $metaDatas           = $this->htmlService->metaData($post['url'], config('common.metaProperty'));
                $response['success'] = true;
                $response['data']    = $metaDatas;
            }
        }
        return response()->json($response);
    }

    public function url(Request $request)
    {
        $response = [
            'success' => false,
            'data'    => [],
            'msg'     => '請確認資料是否正確!',
        ];
        if (Auth::guard('user')->check()) {
            $code    = $request->query('code');
            $urlData = $this->urlRepository->getByUserCode(Auth::guard('user')->id(), $code);
            if ($urlData) {
                $response = [
                    'success' => true,
                    'data'    => [
                        'code'         => $urlData->short_url,
                        'url'          => $urlData->original_url,
                        'title'        => $urlData->og_title,
                        'description'  => $urlData->og_description,
                        'image'        => $urlData->og_image,
                        'ga_id'        => $urlData->gacode_id,
                        'pixel_id'     => $urlData->fbpixel_id,
                        'utm_source'   => $urlData->utm_source,
                        'utm_medium'   => $urlData->utm_medium,
                        'utm_campaign' => $urlData->utm_campaign,
                        'utm_term'     => $urlData->utm_term,
                        'utm_content'  => $urlData->utm_content,
                        'hashtag'      => $urlData->hashtag,
                        'content_type' => $urlData->content_type,
                    ],
                    'msg'     => '',
                ];
            }
        }
        return response()->json($response);
    }

    public function urlDelete(Request $request)
    {
        $response = [
            'success' => false,
            'msg'     => '請確認資料是否正確!',
        ];
        if (Auth::guard('user')->check()) {
            $code = $request->input('code');
            if ($code) {
                $urlData = $this->urlRepository->getByUserCode(Auth::guard('user')->id(), $code);
                if ($urlData) {
                    $this->urlRepository->delete($urlData);
                    $response['success'] = true;
                    $response['msg']     = '刪除成功!';
                }
            }
        }
        return response()->json($response);
    }

    public function urlAnalytics(Request $request)
    {
        $response = [
            'success' => false,
            'data'    => [],
            'msg'     => '請確認資料是否正確!',
        ];
        if (Auth::guard('user')->check()) {
            $code    = $request->query('code');
            $urlData = $this->urlRepository->getByUserCode(Auth::guard('user')->id(), $code);
            if ($urlData) {
                // 取得分析資料
                // referral
                $response['data']['referral'] = $this->logRepository->analyticsReferral($code);
                // os
                $response['data']['os'] = $this->logRepository->analyticsOs($code);

                $response['success'] = true;
                $response['msg']     = '';
            }
        }
        return response()->json($response);
    }

    private function setValidate($request, $user_id = null, $id = null)
    {
        $rules = [
            'url' => 'required|url|max:2048',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
            'hash_tag' => 'nullable|string|max:200|regex:/^[\p{L}\p{N},\s\-_]*$/u',
            'ga_id' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9\-]*$/',
            'pixel_id' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9\-]*$/',
            'source' => 'nullable|string|max:100',
            'medium' => 'nullable|string|max:100',
            'campaign' => 'nullable|string|max:100',
            'term' => 'nullable|string|max:100',
            'content' => 'nullable|string|max:100',
        ];

        if ($request->hasFile('image_file')) {
            $rules['image_file'] = 'image|mimes:jpeg,png,gif,webp|max:5120';
        }

        $attribute = [
            'url'         => '網址',
            'title'       => '標題',
            'description' => '描述',
            'image_file'  => '圖片',
            'hash_tag'    => '標籤',
            'ga_id'       => 'GA ID',
            'pixel_id'    => 'Pixel ID',
            'source'      => 'UTM Source',
            'medium'      => 'UTM Medium',
            'campaign'    => 'UTM Campaign',
            'term'        => 'UTM Term',
            'content'     => 'UTM Content',
        ];

        $messages = [
            'hash_tag.regex' => '標籤只能包含文字、數字、逗號、空格、底線和連字號',
            'ga_id.regex' => 'GA ID 格式不正確',
            'pixel_id.regex' => 'Pixel ID 格式不正確',
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attribute);

        return $validator;
    }

    private function handleImageUpload($image)
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $mimeType = $image->getMimeType();
        $extension = strtolower($image->getClientOriginalExtension());

        if (!in_array($mimeType, $allowedMimes) || !in_array($extension, $allowedExtensions)) {
            return null;
        }

        if ($image->getSize() > 5 * 1024 * 1024) {
            return null;
        }

        $fileName = uniqid('img_') . '.' . $extension;
        $image->move(public_path('/image/url/'), $fileName);

        return $fileName;
    }
}
