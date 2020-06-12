<?php

namespace App\Repositories;

use App\Models\UrlShortener;
use Illuminate\Support\Str;

class UrlShortenerRepository extends BaseRepository
{
    protected $model;

    public function __construct(UrlShortener $model)
    {
        $this->model = $model;
    }

    public function list($params = [])
    {
        $query = $this->model->where('lu_id', $params['lu_id'])
                ->orderBy('id', 'desc');
                
        if (!empty($params['keyword'])) {
            $query->leftJoin('hash_tags', 'url_shortener.id', '=', 'hash_tags.us_id')
                ->where('url_shortener.og_title', 'like', '%' . $params['keyword'] . '%')
                ->orWhere('hash_tags.tag_name', 'like', '%' . $params['keyword'] . '%');
        }
                
        return $query->select('url_shortener.*')
                    ->paginate(config('constants.per_page'));
    }

    public function generateCode($num = 8)
    {
        $code = Str::random($num);
        $shortUrl = $this->model->where('short_url', $code)->first();
        if (!$shortUrl) {
            return $code;
        }
        return $this->generateCode($num);
    }

    public function getByCode($code)
    {
        return $this->model->where('short_url', $code)->first();
    }

    public function getCodeForUpdate($code)
    {
        return $this->model->where('short_url', $code)->lockForUpdate()->first();
    }

    public function getByUserCode($luID, $code)
    {
        return $this->model->where('lu_id', $luID)->where('short_url', $code)->first();
    }

}
