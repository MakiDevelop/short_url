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
        // return $this->model->paginate(config('constants.per_page'));
    }

    public function generateCode()
    {
        $code = Str::random(7);
        $shortUrl = $this->model->where('short_url', $code)->first();
        if (!$shortUrl) {
            return $code;
        }
        return $this->generateCode();
    }

}
