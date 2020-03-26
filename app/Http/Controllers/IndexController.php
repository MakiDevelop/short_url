<?php

namespace App\Http\Controllers;

use App\Repositories\UrlShortenerRepository;
use App\Services\HtmlParserService;
use Illuminate\Http\Request;
use PHPHtmlParser\Dom;

class IndexController extends Controller
{
    protected $client;
    protected $htmlService;
    protected $urlRepository;
    public function __construct(HtmlParserService $htmlService, UrlShortenerRepository $urlRepository)
    {
        $this->client        = new \GuzzleHttp\Client();
        $this->htmlService   = $htmlService;
        $this->urlRepository = $urlRepository;
    }

    public function index()
    {
        return view('index');
    }

    public function urlData($code)
    {
        $data = $this->urlRepository->getByCode($code);
        if ($data) {
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
            $code       = $this->urlRepository->generateCode();
            $metaDatas  = $this->htmlService->metaData($post['url'], config('common.metaProperty'));
            $tmpData = [
                'original_url' => $post['url'],
                'short_url'    => $code,
                'gacode_id'    => '',
                'fbpixel_id'   => '',
                'hashtag'      => '',
            ];
            $insertData = array_merge($tmpData, $metaDatas);
            $urlData  = $this->urlRepository->insert($insertData);
            $response = [
                'success'   => true,
                'code'      => $code,
                'short_url' => url($code),
            ];
        }
        return response()->json($response);
    }

    protected function getWebsite($url)
    {

        $response = $this->client->request('GET', $url);

        echo $response->getStatusCode(); // 200
        echo $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'
        echo $response->getBody();

        try {
            var_dump('iun here');

            $tags = get_meta_tags($url);
            var_dump($tags);

            echo '<br />=====================<br />';

            $html = file_get_contents($url);
            $dom  = new Dom;
            $dom->load($html);
            $metas = $dom->find('meta');
            foreach ($metas as $meta) {
                var_dump($meta->property);
                var_dump($meta->content);
                echo '<br />=====================<br />';
            }

        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        } finally {
            echo "First finally.\n";
        }

    }
}
