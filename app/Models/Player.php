<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model {
    
    use HasSlugTrait;
    
	protected $table = 'players';
    
    public function positions(){
        return $this->belongsToMany('App\Models\Position')->withTimestamps();
    }
    
    public function nflteam(){
        return $this->belongsTo('App\Models\Nflteam');
    }
}