<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlShortener extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'url_shortener';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'us_id';

    // const CREATED_AT = 'creation_date';
    // const UPDATED_AT = 'last_update';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['original_url', 'short_url', 'og_title', 'og_descript', 'og_image', 'gacode_id', 'fbpixel_id', 'hashtag'];
}
