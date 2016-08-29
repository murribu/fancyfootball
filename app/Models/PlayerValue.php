<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerValue extends Model {
    
	protected $table = 'player_values';
    protected $fillable = ['player_id', 'league_id'];
    public function player(){
        return $this->belongsTo('App\Models\Player');
    }
    
    public function league(){
        return $this->belongsTo('App\Models\League');
    }
}