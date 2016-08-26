<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Universe extends Model {

	protected $table = 'universe';
    protected $fillable = ['player_id', 'league_id'];
    public function player(){
        return $this->belongsTo('App\Model\Player');
    }
    
    public function league(){
        return $this->belongsTo('App\Models\League');
    }
}