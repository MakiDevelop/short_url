<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;

class IndexController extends Controller
{
    protected $client;
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function index()
    {
        return view('index');
    }

    public function test()
    {
        $url  = 'https://www.apple.com/tw/';
        $this->getWebsite($url);
    }

    public function checkUrl(Request $request)
    {
        $post = $request->post();
        if (isset($post['url'])) {
            
        }
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
            $dom = new Dom;
            $dom->load($html);
            $metas = $dom->find('meta');
            foreach  ($metas as $meta) {
                var_dump($meta->property);
                var_dump($meta->content);
                echo '<br />=====================<br />';
            }
            
        } catch (\Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        } finally {
            echo "First finally.\n";
        }
        
    }
}
