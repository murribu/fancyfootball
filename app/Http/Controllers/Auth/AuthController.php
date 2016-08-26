<?php

namespace App\Http\Controllers\Auth;

use Auth;
use DB;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;

use App\Models\FacebookToken;
use App\Models\FacebookUser;
use App\Models\User;


class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    
    public function redirectToProvider(){
        return Socialite::driver('facebook')->redirect();
    }
    
    public function handleProviderCallback(){
        $login_user = Socialite::driver('facebook')->user();
        
        $user = User::create_from_facebook_login($login_user);
        
        Auth::loginUsingId($user->id);
        return view('killwindow');
    }
    
    public function getMe(){
        $user = Auth::user();
        $return = [
            'logged_in' => 0,
        ];
        if ($user){
            $return['logged_in'] = 1;
            $return['email'] = $user->email;
            $return['name'] = $user->name;
            if ($user->facebook_user){
                $return['email'] = $user->facebook_user->email;
                $return['name'] = $user->facebook_user->name;
                $return['avatar'] = $user->facebook_user->avatar;
            }
        }
        
        return $return;
    }
    
    public function logout(){
        Auth::logout();
        
        return view('killwindow');
    }
    
    public function loginMe($id = 1){
        $user = Auth::user();
        if ($user){
            echo('logged in');
        }else{
            echo('logging in...');
        }
        $user = Auth::loginUsingId($id);
        return $user;
    }
}
