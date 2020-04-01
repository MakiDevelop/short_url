<?php

namespace App\Repositories;

use App\Models\LoginUser;

class LoginUserRepository extends BaseRepository
{
    protected $model;

    public function __construct(LoginUser $model)
    {
        $this->model = $model;
    }

    public function list($params = [])
    {
        // return $this->model->paginate(config('constants.per_page'));
    }

    public function getByOauthID($oauthType, $oauthID)
    {
        return $this->model->where('oauth_type', $oauthType)->where('oauth_id', $oauthID)->first();
    }
}
