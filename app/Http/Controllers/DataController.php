<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\Player;
use App\Models\Universe;

class DataController extends Controller
{
    public function getPlayers(){
        $players = Player::leftJoin('nflteams', 'nflteams.id', '=', 'players.nflteam_id')
            ->leftJoin('player_position', 'players.id', '=', 'player_position.player_id')
            ->leftJoin('positions', 'positions.id', '=', 'player_position.position_id')
            ->selectRaw('players.*, nflteams.espn_abbr, positions.abbr position')
            ->limit(100)
            ->get();
        foreach($players as $k=>$player){
            $players[$k]->attributes = $player->attributes();
        }
        return $players;
    }
    
    public function getPlayer($slug){
        $player = Player::where('slug', $slug)->first();
        if ($player && Auth::user() && Auth::user()->league()){
            $player->in_universe = Universe::where('player_id', $player->id)
                ->where('league_id', Auth::user()->league()->id)
                ->where('active', '1')
                ->count() > 0;
        }else{
            $player->in_universe = false;
        }
        return $player;
    }
    
    public function postToggleUniverse(){
        $player = Player::where('slug', Input::get('player'))->first();
        if ($player && Auth::user() && Auth::user()->league()){
            $u = Universe::firstOrCreate(['player_id' => $player->id, 'league_id' => Auth::user()->league()->id]);
            
            $u->active = !$u->active;
            $u->save();
            $player->in_universe = $u->active;
        }
        
        return $player;
    }
}
