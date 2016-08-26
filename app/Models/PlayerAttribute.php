<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerAttribute extends Model {
    
	protected $table = 'player_attributes';
    
    protected $fillable = ['name'];
    
}