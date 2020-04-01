<?php

namespace App\Http\Controllers;

use App\Repositories\ClickLogRepository;
use App\Repositories\UrlShortenerRepository;
use App\Services\HtmlParserService;
use Illuminate\Http\Request;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Auth;

class IndexController extends Controller
{
    protected $client;
    protected $htmlService;
    protected $urlRepository, $logRepository;
    public function __construct(HtmlParserService $htmlService, UrlShortenerRepository $urlRepository, ClickLogRepository $logRepository)
    {
        $this->client        = new \GuzzleHttp\Client();
        $this->htmlService   = $htmlService;
        $this->urlRepository = $urlRepository;
        $this->logRepository = $logRepository;
    }

    public function index()
    {
        if (Auth::id()) {
            
            exit;
        }
        return view('index');
    }

    public function urlData(Request $request, $code)
    {
        $data = $this->urlRepository->getByCode($code);
        if ($data) {
            $os      = new Os();
            $browser = new Browser();
            // 記錄log
            $insertData = [
                'us_id'       => '0',
                'short_url'   => $code,
                'referral'    => $request->server('HTTP_REFERER'),
                'os'          => $os->getName(),
                'browser'     => $browser->getName(),
                'user_agenet' => $request->header('User-Agent'),
                'ip'          => $request->ip(),
                'click_time'  => date('Y-m-d H:i:s'),
            ];
            $this->logRepository->insert($insertData);

            return view('url', compact('data'));
        }
        return redirect('/');
    }

    public function test()
    {
        $url = 'https://www.apple.com/tw/';
        // $this->getWebsite($url);
        $metaDatas = $this->htmlService->metaData($url, config('common.metaProperty'));
        var_dump($metaDatas);
        $code = $this->urlRepository->generateCode();
        var_dump($code);

        var_dump(url($code));

        $insertData = [
            'original_url' => $url,
            'short_url'    => $code,
            'gacode_id'    => '',
            'fbpixel_id'   => '',
            'hashtag'      => '',
        ];

    }

    public function shortUrl(Request $request)
    {
        $response = [
            'success' => false,
        ];
        $post = $request->post();
        if (isset($post['url'])) {
            $code      = $this->urlRepository->generateCode();
            $metaDatas = $this->htmlService->metaData($post['url'], config('common.metaProperty'));
            $tmpData   = [
                'original_url' => $post['url'],
                'short_url'    => $code,
                'gacode_id'    => '',
                'fbpixel_id'   => '',
                'hashtag'      => '',
                'ip'           => $request->ip(),
            ];
            $insertData = array_merge($tmpData, $metaDatas);
            $urlData    = $this->urlRepository->insert($insertData);
            $response   = [
                'success'   => true,
                'code'      => $code,
                'short_url' => url($code),
            ];
        }
        return response()->json($response);
    }
}
