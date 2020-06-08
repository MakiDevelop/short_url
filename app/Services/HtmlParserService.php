<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPHtmlParser\Dom;

class HtmlParserService
{
    protected $client;
    public function __construct()
    {
        $this->client = new Client();
    }

    public function metaData($url, $metaProperty, $code = '')
    {
        $metaDatas = [];
        $options = [];
        $cookieJar = $this->checkSiteCookie($url);
        if ($cookieJar) {
            $options['cookies'] = $cookieJar;
        }

        $response  = $this->client->request('GET', $url, $options);
        $contentType = $response->getHeader('content-type')[0];
        $html      = $response->getBody();
        try {
            $metaDatas['content_type'] = $contentType;
            if (strpos($contentType, 'image') !== false) {
                $metaDatas['og_image'] = $url;
                $metaDatas['og_title'] = '';
            } else {
                $dom = new Dom;
                $dom->load($html);
                $metas = $dom->find('meta');
                foreach ($metas as $meta) {
                    if (in_array($meta->property, $metaProperty)) {
                        $key = str_replace(':', '_', $meta->property);
                        $metaDatas[$key] = $meta->content;
                    }
                }
                if (empty($metaDatas['og_title'])) {
                    $metaDatas['og_title'] = $dom->find('title')->text;
                }
                if (strpos($url, 'drive.google') !== false && empty($metaDatas['og_image'])) {
                    $metaDatas['og_image'] = 'https://www.gstatic.com/images/branding/product/1x/drive_48dp.png';
                }
            }
        } catch (\Exception $e) {

        }
        return $metaDatas;
    }

    private function checkSiteCookie($url)
    {
        $cookieJar = '';
        if (strpos($url, 'ptt.cc') !== false) {
            $cookieJar = CookieJar::fromArray([
                'over18' => '1'
            ], 'www.ptt.cc');
        }
        return $cookieJar;
    }

    private function processMetaData()
    {

    }

}
