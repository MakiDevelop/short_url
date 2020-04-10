<?php

namespace App\Http\Controllers;

use App\Repositories\ClickLogRepository;
use App\Repositories\HashTagsRepository;
use App\Repositories\UrlShortenerRepository;
use App\Services\HtmlParserService;
use Auth;
use Illuminate\Http\Request;
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
            $urlData   = $this->urlRepository->getByUserCode(Auth::guard('user')->id(), $post['code']);
            $validator = $this->setValidate($request, Auth::guard('user')->id(), $urlData->id);
            if ($urlData && $validator->passes()) {
                $updateData = [
                    'original_url'   => $post['url'],
                    'og_title'       => $post['title'],
                    'og_description' => $post['description'],
                    'og_image'       => $post['image'] ?? '',
                    'gacode_id'      => $post['ga_id'] ?? '',
                    'fbpixel_id'     => $post['pixel_id'] ?? '',
                    'hashtag'        => $post['hash_tag'] ?? '',
                ];
                if ($request->hasFile('image_file')) {
                    $post  = $request->input();
                    $image = $request->file('image_file');
                    // $fileName = $image->getClientOriginalName();
                    $fileName = uniqid('img_') . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('/image/url/'), $fileName);
                    $updateData['og_image'] = $fileName;
                    $response['image']      = $fileName;
                } else {
                    $response['image'] = 'not have';
                }
                $response['file'] = $request->file('image_file');
                $response['post'] = $post;
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
            $validator = $this->setValidate($request, Auth::guard('user')->id());
            if ($validator->passes()) {
                $code = $this->urlRepository->generateCode();

                $tmpData = [
                    'lu_id'        => Auth::guard('user')->id() ?? 0,
                    'original_url' => $post['url'],
                    'short_url'    => $code,
                    'gacode_id'    => $post['ga_id'] ?? '',
                    'fbpixel_id'   => $post['pixel_id'] ?? '',
                    'hashtag'      => $post['hash_tag'] ?? '',
                    'ip'           => $request->ip(),
                ];
                if (Auth::guard('user')->check()) {
                    $metaDatas = [
                        'og_title'       => $post['title'],
                        'og_description' => $post['description'],
                        'og_image'       => $post['image'] ?? '',
                    ];
                    if ($request->hasFile('image_file')) {
                        $post  = $request->input();
                        $image = $request->file('image_file');
                        // $fileName = $image->getClientOriginalName();
                        $fileName = uniqid('img_') . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('/image/url/'), $fileName);
                        $metaDatas['og_image'] = $fileName;
                    }
                } else {
                    $metaDatas = $this->htmlService->metaData($post['url'], config('common.metaProperty'));
                }

                $insertData = array_merge($tmpData, $metaDatas);
                $urlData    = $this->urlRepository->insert($insertData);

                // hash tag
                if (Auth::guard('user')->check()) {
                    $tags = explode(',', $post['hash_tag']);
                    if (count($tags)) {
                        $this->tagsRepository->processTags($urlData->id, $tags);
                    }
                }

                $response = [
                    'success'   => true,
                    'code'      => $code,
                    'short_url' => url($code),
                ];
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
                        'code'        => $urlData->short_url,
                        'url'         => $urlData->original_url,
                        'title'       => $urlData->og_title,
                        'description' => $urlData->og_description,
                        'image'       => $urlData->og_image,
                        'ga_id'       => $urlData->gacode_id,
                        'pixel_id'    => $urlData->fbpixel_id,
                        'hashtag'     => $urlData->hashtag,
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
                $osData                 = $this->logRepository->analyticsOs($code);
                $response['data']['os'] = $osData->toArray();

                $response['success'] = true;
                $response['msg']     = '';
            }
        }
        return response()->json($response);
    }

    public function test()
    {
        if (env('APP_ENV') == 'local') {
            // $urlData = $this->urlRepository->getByID(14);
            // var_dump($urlData->toArray());
            // var_dump($urlData->tags->toArray());

            // $url = \App\Models\HashTags::withTrashed()->where('us_id', 14)->where('tag_name', 'Apple')->first();
            // var_dump($url);

            $referralData = $this->logRepository->analyticsReferral('3b2DPwGw');
            var_dump($referralData->toArray());
        }

        // $image = 'https://store.storeimages.cdn-apple.com/8756/as-images.apple.com/is/ipad-pro-og-202003?wid=1200&amp;hei=630&amp;fmt=jpeg&amp;qlt=95&amp;op_usm=0.5,0.5&amp;.v=1583201083141';
        // $pos   = strpos($image, 'http');
        // var_dump($pos);
    }

    private function setValidate($request, $user_id = null, $id = null)
    {
        $rules = [
            'url' => 'required|url',
        ];

        if (empty($id)) {

        } else {

        }

        if ($user_id) {

        }

        $attribute = [
            'url'         => '網址',
            'title'       => 'og:title',
            'description' => 'og:description',
        ];

        $validator = Validator::make($request->input(), $rules, [], $attribute);

        return $validator;
    }
}
