<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class ApiSettingsController extends Controller
{
    public function index()
    {
        if (!Auth::guard('user')->check()) {
            return redirect('/login');
        }

        $user = Auth::guard('user')->user();

        return view('api_settings', compact('user'));
    }

    public function generateToken(Request $request)
    {
        if (!Auth::guard('user')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('user')->user();
        $token = $user->generateApiToken();

        return response()->json([
            'success' => true,
            'api_token' => $token,
        ]);
    }
}
