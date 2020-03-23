<?php

namespace App\Repositories;

use App\Models\UrlShortener;

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

}
