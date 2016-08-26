<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class League extends Model {
    
    use HasSlugTrait;
    
	protected $table = 'leagues';
    
    public function league_attributes_values(){
        return $this->hasMany('App\Models\LeagueAttributeValue');
    }
    
    public function attributes(){
        $att = [];
        foreach($this->league_attributes_values as $v){
            $att[$v->league_attribute->name] = $v->value;
        }
        
        return $att;
    }
    
    public function attribute($key, $default = null){
        $row = LeagueAttributeValue::where('league_id', $this->id)
            ->whereRaw('league_attribute_id in (select id from league_attributes where name = ?)', array($key))
            ->first();
        if ($row){
            return $row->value;
        }else{
            return $default;
        }
    }
    
    public function set_attribute($key, $value){
        $pa = LeagueAttribute::firstOrCreate(['name' => $key]);
        $pav = LeagueAttributeValue::where('league_id', $this->id)
            ->where('league_attribute_id', $pa->id)
            ->first();
        if (!$pav){
            $pav = new LeagueAttributeValue;
            $pav->league_id = $this->id;
            $pav->league_attribute_id = $pa->id;
        }
        $pav->value = $value;
        $pav->save();
            
        return $pav;
    }
}