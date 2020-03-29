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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'IndexController@index');
Route::get('/index/test', 'IndexController@test');
Route::post('/index/short_url', 'IndexController@shortUrl');

Route::get('/login', 'LoginController@index');
Route::get('/login/facebook', 'LoginController@facebook');
Route::get('/login/facebook_callback', 'LoginController@facebookCallback');
Route::get('/login/facebook_cancel', 'LoginController@facebookCancel');

Route::get('/policies/privacy', 'PoliciesController@privacy');
Route::get('/policies/terms', 'PoliciesController@terms');

Route::get('{code}', 'IndexController@urlData');

