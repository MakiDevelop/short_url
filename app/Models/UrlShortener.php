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
    protected $fillable = ['original_url', 'short_url', 'lu_id', 'og_title', 'og_description', 'og_image', 'content_type', 'gacode_id', 'fbpixel_id', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'clicks', 'hashtag', 'ip'];

    public function getOgImageAttribute($value)
    {
        if (strpos($value, 'http') !== false) {
            return $value;
        }
        return url('/image/url/' . $value);
    }

    public function tags()
    {
        return $this->hasMany('App\Models\HashTags', 'us_id', 'id');
    }

    public function getOriginalUrlAttribute($value)
    {
        $urlData = parse_url($value);
        $queryData =  [];
        if (isset($urlData['query'])) {
            parse_str($urlData['query'], $queryData);
        }
        foreach (config('common.utmData') as $utm) {
            if (isset($this->attributes[$utm]) && $this->attributes[$utm] != '') {
                $queryData[$utm] = $this->attributes[$utm];
            }
        }
        if ($queryData) {
            $urlData['query'] = http_build_query($queryData);
        }
        return $this->unparse_url($urlData);
    }

    public function unparse_url($parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
