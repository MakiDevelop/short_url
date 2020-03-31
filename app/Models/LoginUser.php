<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_user';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'us_id';

    // const CREATED_AT = 'click_time';
    // const UPDATED_AT = 'last_update';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['oauth_type', 'oauth_email', 'oauth_id', 'oauth_first_time', 'oauth_last_login'];
}
