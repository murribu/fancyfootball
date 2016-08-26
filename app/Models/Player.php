<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model {
    
    use HasSlugTrait;
    
	protected $table = 'players';
    
    public function positions(){
        return $this->belongsToMany('App\Models\Position')->withTimestamps();
    }
    
    public function nflteam(){
        return $this->belongsTo('App\Models\Nflteam');
    }
    
    public function player_attributes_values(){
        return $this->hasMany('App\Models\PlayerAttributeValue');
    }
    
    public function attributes(){
        $att = [];
        foreach($this->player_attributes_values as $v){
            $att[$v->player_attribute->name] = $v->value;
        }
        
        return $att;
    }
    
    public function attribute($key, $default = null){
        $row = PlayerAttributeValue::where('player_id', $this->id)
            ->whereRaw('player_attribute_id in (select id from player_attributes where name = ?)', array($key))
            ->first();
        if ($row){
            return $row->value;
        }else{
            return $default;
        }
    }
    
    public function set_attribute($key, $value){
        $pa = PlayerAttribute::firstOrCreate(['name' => $key]);
        $pav = PlayerAttributeValue::where('player_id', $this->id)
            ->where('player_attribute_id', $pa->id)
            ->first();
        if (!$pav){
            $pav = new PlayerAttributeValue;
            $pav->player_id = $this->id;
            $pav->player_attribute_id = $pa->id;
        }
        $pav->value = $value;
        $pav->save();
            
        return $pav;
    }
}