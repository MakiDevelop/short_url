<?php

namespace App\Repositories;

use App\Models\ClickLog;
use Illuminate\Support\Str;

class ClickLogRepository extends BaseRepository
{
    protected $model;

    public function __construct(ClickLog $model)
    {
        $this->model = $model;
    }

    public function list($params = [])
    {
        // return $this->model->paginate(config('constants.per_page'));
    }
}
