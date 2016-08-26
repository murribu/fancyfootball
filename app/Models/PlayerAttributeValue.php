<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerAttributeValue extends Model {
    
	protected $table = 'player_attribute_values';
    
    public function player(){
        return $this->belongsTo('App\Models\Player');
    }
    
    public function player_attribute(){
        return $this->belongsTo('App\Models\PlayerAttribute');
    }
}