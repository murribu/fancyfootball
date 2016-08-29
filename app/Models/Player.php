<?php

namespace App\Models;
use DB;
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
            if (intval($v->value) == $v->value){
                $att[$v->player_attribute->name] = intval($v->value);
            }else{
                $att[$v->player_attribute->name] = $v->value;
            }
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
    
    public function projected_stats(){
        return $this->hasOne('App\Models\ProjectedStat');
    }
    
    public function player_values($league_id = null){
        if ($league_id){
            return $this->hasMany('App\Models\PlayerValue')->where('league_id', $league_id)->first();
        }else{
            return $this->hasMany('App\Models\PlayerValue');
        }
    }
    
    public function calculate_projected_points($league){
        $offense_stats = [
            'passing_yard' => 'passing_yards',
            'passing_td' => 'passing_tds',
            'passing_interception' => 'passing_ints',
            'rushing_yard' => 'rushing_yards',
            'rushing_td' => 'rushing_tds',
            'receiving_yard' => 'receiving_yards',
            'receiving_td' => 'receiving_tds',
            'fumble_lost' => 'fumbles'
        ];
        $defense_stats = [
            'sack' => 'defense_sacks',
            'defense_interception' => 'defense_ints',
            'fumble_recovery' => 'defense_fumble_recoveries',
            'defense_touchdown' => 'defense_tds'
        ];
        $kicker_stats = [
            'field_goal_1_39' => 'fg_1_39_made',
            'field_goal_40_49' => 'fg_40_49_made',
            'field_goal_50' => 'fg_50_made',
            'extra_point' => 'extra_points_made'
        ];
        
        $points = 0;
        $proj = $this->projected_stats;
        $pv = false;
        if (count($this->positions) > 0){
            switch ($this->positions[0]->type){
                case 'offense':
                    foreach($offense_stats as $s=>$db){
                        // echo $db." - ".$proj->{$db}."*".  $league->attribute($s)."<br>";
                        $points += $proj->{$db} * $league->attribute($s);
                    }
                    break;
                case 'defense':
                    foreach($defense_stats as $s=>$db){
                        // echo $db." - ".$proj->{$db}."*".  $league->attribute($s)." = ".$proj->{$db} * $league->attribute($s)."<br>";
                        $points += $proj->{$db} * $league->attribute($s);
                    }
                    //Calculate Points Against
                    if ($proj->defense_points_against > 0 && $proj->defense_points_against < 6*16){
                        $points += $league->attribute('points_allowed_1_6')*16;
                    }else if($proj->defense_points_against < 13*16){
                        $points += $league->attribute('points_allowed_7_13')*16;
                    }else if($proj->defense_points_against < 20*16){
                        $points += $league->attribute('points_allowed_14_20')*16;
                    }else if($proj->defense_points_against < 27*16){
                        $points += $league->attribute('points_allowed_21_27')*16;
                    }else if($proj->defense_points_against < 34*16){
                        $points += $league->attribute('points_allowed_28_24')*16;
                    }else{
                        $points += $league->attribute('points_allowed_35')*16;
                    }
                    // echo "points against = ".$proj->defense_points_against;
                    // dd($points);
                    break;
                case 'kicker':
                    foreach($kicker_stats as $s=>$db){
                        $points += $proj->{$db} * $league->attribute($s);
                    }
                    break;
                default:
                    return ['error' => 'bad position for '. $this->first_name.' '.$this->last_name];
                    break;
            }
            
            $pv = PlayerValue::firstOrCreate(['player_id' => $this->id, 'league_id' => $league->id]);
            $pv->points = $points;
            $pv->save();
        }

        return $pv;
    }
}