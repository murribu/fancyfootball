<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaguePlayer extends Model {
    
	protected $table = 'league_player';
    protected $fillable = ['league_id', 'player_id'];
    public function league(){
        return $this->belongsTo('App\Models\League');
    }
    
    public function player(){
        return $this->belongsTo('App\Models\Player');
    }
}