<?php

namespace App\Http\Controllers;

use App\Repositories\LoginUserRepository;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Socialite;

class LoginController extends Controller
{
    private $userRepository;

    public function __construct(LoginUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return view('login');
    }

    public function logout()
    {
        Auth::guard('user')->logout();
        return redirect('/');
    }

    public function oauth($type = '')
    {
        if (in_array($type, config('common.socialTypes'))) {
            return Socialite::driver($type)->redirect();
        }
        return redirect('/');
    }

    public function oauthBack($type = '')
    {
        if (in_array($type, config('common.socialTypes'))) {
            try {
                $oauthUser = Socialite::driver($type)->user();

                $user = $this->userRepository->getByOauthID($type, $oauthUser->id);
                if ($user) {
                    $user->oauth_last_login = date('Y-m-d H:i:s');
                    $user->save();
                } else {
                    $inserData = [
                        'oauth_type'       => $type,
                        'oauth_id'         => $oauthUser->id,
                        'oauth_name'       => $oauthUser->name,
                        'oauth_email'      => $oauthUser->email,
                        'oauth_first_time' => date('Y-m-d H:i:s'),
                    ];
                    $user = $this->userRepository->insert($inserData);
                }

                Log::info('OAuth login successful', [
                    'type' => $type,
                    'user_id' => $user->id
                ]);

                Auth::guard('user')->login($user);
                return redirect('/');
            } catch (Exception $e) {
                Log::error('OAuth login failed', [
                    'type' => $type,
                    'error' => $e->getMessage()
                ]);
            }
        }
        return redirect('/');
    }

    public function facebookCancel()
    {
        return redirect('/login');
    }
}
