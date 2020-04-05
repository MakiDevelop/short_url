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
            $params = $querys = $request->query();
            $params['lu_id'] = Auth::guard('user')->id();
            $lists = $this->urlRepository->list($params);
            return view('user_index', compact('lists', 'querys'));
        }
        return view('index');
    }

    public function urlData(Request $request, $code)
    {
        $data = $this->urlRepository->getCodeForUpdate($code);
        if ($data) {
            $os      = new Os();
            $browser = new Browser();
            // 記錄log
            $insertData = [
                'us_id'       => $data->id,
                'short_url'   => $code,
                'referral'    => $request->server('HTTP_REFERER'),
                'os'          => $os->getName(),
                'browser'     => $browser->getName(),
                'user_agenet' => $request->header('User-Agent'),
                'ip'          => $request->ip(),
                'click_time'  => date('Y-m-d H:i:s'),
            ];
            $this->logRepository->insert($insertData);
            // update clicks number
            $data->clicks += 1;
            $data->save();

            return view('url', compact('data'));
        }
        return redirect('/');
    }

    public function shortUrl(Request $request)
    {
        $response = [
            'success' => false,
        ];
        $post = $request->post();
        if (isset($post['url'])) {
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
                    $post     = $request->input();
                    $image    = $request->file('image_file');
                    // $fileName = $image->getClientOriginalName();
                    $fileName = uniqid('img_') . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('/image/url'), $fileName);
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
        $post = $request->post();
        if ($post && isset($post['url'])) {
            $metaDatas           = $this->htmlService->metaData($post['url'], config('common.metaProperty'));
            $response['success'] = true;
            $response['data']    = $metaDatas;
        }

        return response()->json($response);
    }

    public function urlDelete()
    {
        if (Auth::guard('user')->check()) {

        }   
    }

    public function test()
    {
        if (env('APP_ENV') == 'local') {
            $urlData = $this->urlRepository->getByID(14);
            var_dump($urlData->toArray());
            var_dump($urlData->tags->toArray());

            $url = \App\Models\HashTags::withTrashed()->where('us_id', 14)->where('tag_name', 'Apple')->first();
            var_dump($url);
        }
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
