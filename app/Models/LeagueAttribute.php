<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueAttribute extends Model {
    
	protected $table = 'league_attributes';
    
    protected $fillable = ['name'];
    
}