<?php

namespace App\Models;
use DB;
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
    
    public function calculateValues(){
        $players = Player::orderBy('players.id')->get();
        
        foreach($players as $p){
            $p->calculate_projected_points($this);
        }
        
        $replacement_levels = [
            'qb' => 0,
            'rb' => 0,
            'wr' => 0,
            'te' => 0,
            'k' => 0,
            'd-st' => 0,
        ];
        $limit = 5;
        // DB::enableQueryLog();
        foreach($replacement_levels as $pos=>$points){
            $values = PlayerValue::join('player_attribute_values', 'player_attribute_values.player_id', '=', 'player_values.player_id')
                ->join('player_attributes', 'player_attributes.id', '=', 'player_attribute_values.player_attribute_id')
                ->where('player_attributes.name', '=', 'espn_rank')
                ->whereRaw('player_values.player_id in (select player_id from player_position where position_id = (select id from positions where slug = ?))', [$pos])
                ->whereRaw('player_values.player_id not in (select player_id from universe where active = 1 and league_id = ?)', [$this->id])
                ->select('player_values.id', 'player_values.points')
                ->orderByRaw('cast(player_values.points as signed) desc, cast(player_attribute_values.value as signed)')
                ->limit($limit)
                ->get();
            $avg = 0;
            // dd(DB::getQueryLog());
            foreach($values as $v){
                $avg += $v->points;
            }
            
            $replacement_levels[$pos] = $avg/$limit;
        }
        
        foreach($players as $p){
            if (count($p->player_values($this->id)) > 0){
                $proj = $p->player_values($this->id);
                $proj->points_above_replacement = 
                    $proj->points - $replacement_levels[$p->positions[0]->slug];
                $proj->save();
            }
        }
        // dd(DB::getQueryLog());
        return $replacement_levels;
    }
}