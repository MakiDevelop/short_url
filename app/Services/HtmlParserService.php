<?php

namespace App\Services;

use GuzzleHttp\Client;
use PHPHtmlParser\Dom;

class HtmlParserService
{
    protected $client;
    public function __construct()
    {
        $this->client = new Client();
    }

    public function metaData($url, $metaProperty)
    {
        $metaDatas = [];
        $response  = $this->client->request('GET', $url);
        $html      = $response->getBody();
        try {
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
        } catch (\Exception $e) {

        }
        return $metaDatas;
    }

}
