<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'click_log';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'us_id';

    public $timestamps = false;
    // const CREATED_AT = 'click_time';
    // const UPDATED_AT = 'last_update';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['us_id', 'short_url', 'referral_url', 'referral', 'os', 'browser', 'user_agent', 'click_time', 'ip'];
}
