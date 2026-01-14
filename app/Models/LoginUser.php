<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LoginUser extends Authenticatable
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
    protected $fillable = ['oauth_type', 'oauth_id', 'oauth_name', 'oauth_email', 'oauth_first_time', 'oauth_last_login', 'api_token'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['api_token'];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return '';
    }

    /**
     * Generate a new API token for the user.
     *
     * @return string
     */
    public function generateApiToken()
    {
        $this->api_token = \Illuminate\Support\Str::random(64);
        $this->save();
        return $this->api_token;
    }
}
