<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'IndexController@index');
Route::get('/index/url', 'IndexController@url');
Route::get('/index/url_analytics', 'IndexController@urlAnalytics');

// Rate limited routes for URL creation/modification
Route::middleware(['throttle:30,1'])->group(function () {
    Route::post('/index/short_url', 'IndexController@shortUrl');
    Route::post('/index/website', 'IndexController@website');
    Route::post('/index/url_delete', 'IndexController@urlDelete');
});

// Rate limited OAuth routes
Route::middleware(['throttle:10,1'])->group(function () {
    Route::get('/login', 'LoginController@index');
    Route::get('/login/oauth/{type?}', 'LoginController@oauth');
    Route::get('/login/oauth_back/{type?}', 'LoginController@oauthBack');
    Route::get('/login/facebook_cancel', 'LoginController@facebookCancel');
});

Route::get('/logout', 'LoginController@logout');

// API Settings
Route::get('/api/settings', 'ApiSettingsController@index');
Route::post('/api/generate-token', 'ApiSettingsController@generateToken');

Route::get('/policies/privacy', 'PoliciesController@privacy');
Route::get('/policies/terms', 'PoliciesController@terms');

Route::get('{code}', 'IndexController@urlData');

