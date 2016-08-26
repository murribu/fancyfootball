<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookToken extends Model
{
	protected $table = 'facebook_tokens';
    
    public function facebook_user(){
        return $this->belongsTo('App\Models\FacebookUser');
    }
}
