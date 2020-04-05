<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UrlShortener extends Model
{
    use SoftDeletes;
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
    protected $fillable = ['original_url', 'short_url', 'lu_id', 'og_title', 'og_description', 'og_image', 'gacode_id', 'fbpixel_id', 'clicks', 'hashtag', 'ip'];

    public function getOgImageAttribute($value)
    {
        if (strpos('http', $value) !== false) {
            return $value;
        }
        return url('/image/url/' . $value);
    }

    public function tags()
    {
        return $this->hasMany('App\Models\HashTags', 'us_id', 'id');
    }
}
