<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API v1 Routes
Route::prefix('v1')->group(function () {

    // Public endpoints (with optional auth for tracking)
    Route::middleware(['throttle:60,1', 'api.token:optional'])->group(function () {
        Route::post('/urls', 'Api\UrlController@store');
        Route::get('/urls/{code}', 'Api\UrlController@show');
        Route::get('/urls/{code}/analytics', 'Api\UrlController@analytics');
    });

    // Authenticated endpoints
    Route::middleware(['throttle:60,1', 'api.token'])->group(function () {
        Route::get('/urls', 'Api\UrlController@index');
        Route::delete('/urls/{code}', 'Api\UrlController@destroy');

        // User info
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->oauth_name,
                    'email' => $request->user()->oauth_email,
                ],
            ]);
        });

        // Generate new API token
        Route::post('/token/refresh', function (Request $request) {
            $user = $request->user();
            $user->api_token = \Illuminate\Support\Str::random(64);
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'api_token' => $user->api_token,
                ],
            ]);
        });
    });
});
