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

    public function facebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookBack()
    {
        $user = Socialite::driver('facebook')->user();
        var_dump($user->name);
        dd($user);
    }

    public function google()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleBack()
    {
        $user = Socialite::driver('google')->user();
        var_dump($user);
    }

    public function facebookCancel()
    {

    }

    public function google()
    {

    }

    public function googleBack()
    {

    }
}
