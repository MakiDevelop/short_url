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

    public function processTags($urlID, $tags)
    {
        $this->model->where('us_id', $urlID)->delete();
        foreach ($tags as $tag) {
            $tagName = trim($tag);
            $tagData = $this->model->withTrashed()->where('us_id', $urlID)->where('tag_name', $tagName)->first();
            if ($tagData) {
                $tagData->deleted_at = null;
                $tagData->save();
            } else {
                $this->insert(['us_id' => $urlID, 'tag_name' => $tagName]);
            }
        }
    }

}
