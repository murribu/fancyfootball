<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookUser extends Model
{
	protected $table = 'facebook_users';
    
    public function user(){
        return $this->hasOne('App\Models\User');
    }
}
