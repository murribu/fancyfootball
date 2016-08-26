<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueAttributeValue extends Model {
    
	protected $table = 'league_attribute_values';
    
    public function league(){
        return $this->belongsTo('App\Models\League');
    }
    
    public function league_attribute(){
        return $this->belongsTo('App\Models\LeagueAttribute');
    }
}