<?php

namespace App\Repositories;

use App\Models\HashTags;
use Illuminate\Support\Str;

class HashTagsRepository extends BaseRepository
{
    protected $model;

    public function __construct(HashTags $model)
    {
        $this->model = $model;
    }

    public function list($params = [])
    {
        // return $this->model->paginate(config('constants.per_page'));
    }

}
