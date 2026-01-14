<?php

namespace App\Http\Middleware;

use App\Models\LoginUser;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $optional
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $optional = null)
    {
        $token = $request->bearerToken() ?? $request->query('api_token');

        if ($token) {
            $user = LoginUser::where('api_token', $token)->first();

            if ($user) {
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
            }
        }

        if ($optional !== 'optional' && !$request->user()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Please provide a valid API token.',
            ], 401);
        }

        return $next($request);
    }
}
