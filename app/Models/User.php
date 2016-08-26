<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;

class User extends Authenticatable
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    private static $rules = [
        'email' => 'required|unique:users,email'
    ];
    
    public function validate($data, $user_id = 0){
        $rules = self::$rules;
        $rules['email'] .= ','.$user_id;
        $v = Validator::make($data, $rules);
        return $v;
    }
    
    public static function create_from_facebook_login($login_user){
        
        $facebook_user = FacebookUser::where('facebook_id', $login_user->id)->first();
        if ($facebook_user){
            $user = $facebook_user->user;
        }else{
            $facebook_user = new FacebookUser;
            $facebook_user->name = $login_user->name;
            $facebook_user->email = $login_user->email;
            $facebook_user->avatar = $login_user->avatar;
            $facebook_user->facebook_id = $login_user->id;
            $facebook_user->save();
            $user = User::where('email', $login_user->email)->first();
            if (!$user){
                $user = new User;
                $user->email = $login_user->email;
                $user->name = $login_user->name;
                $user->facebook_user_id = $facebook_user->id;
            }
        }
        
        $user->last_login = date("Y-m-d H:i:s");
        $user->save();
        
        return $user;
    }
    
    public function facebook_user(){
        return $this->belongsTo('App\Models\FacebookUser');
    }
    
    public function leagues(){
        return $this->hasMany('App\Models\League');
    }
    
    public function league(){
        return $this->leagues()->orderBy('active', 'desc')->first();
    }
    
    public function getRememberToken()
    {
        return null; // not supported
    }

    public function setRememberToken($value)
    {
        // not supported
    }

    public function getRememberTokenName()
    {
        return null; // not supported
    }

    /**
    * Overrides the method to ignore the remember token.
    */
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute)
        {
            parent::setAttribute($key, $value);
        }
    }
}
