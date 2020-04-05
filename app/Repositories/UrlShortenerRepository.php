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
        return $this->model->where('lu_id', $params['lu_id'])
                ->orderBy('id', 'desc')
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

}
