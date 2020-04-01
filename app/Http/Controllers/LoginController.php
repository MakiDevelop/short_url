<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;

class LoginController extends Controller
{

    public function __construct()
    {

    }

    public function test()
    {
        echo 'test';
    }

    public function index()
    {
        return view('login');
    }

    public function oauth($type = '')
    {
        if (in_array($type, config('common.socialTypes'))){
            return Socialite::driver($type)->redirect();
        }
        return redirect('/');
    }

    public function oauthBack($type = '')
    {
        if (in_array($type, config('common.socialTypes'))){
            $user = Socialite::driver($type)->user();
            var_dump($user->name);
            var_dump($user->id);
            var_dump($user->email);
            dd($user);
        }
        return redirect('/');
    }

    public function facebookCancel()
    {

    }
}
