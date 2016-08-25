<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nflteam extends Model {
    
    use HasSlugTrait;
    
	protected $table = 'nflteams';
    
}