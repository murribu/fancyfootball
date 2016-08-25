<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectedStat extends Model {
    
	protected $table = 'projected_stats';
    
    public function player(){
        return $this->belongsTo('App\Models\Player');
    }
}