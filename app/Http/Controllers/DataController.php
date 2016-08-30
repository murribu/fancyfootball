<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\LeaguePlayer;
use App\Models\Player;
use App\Models\PlayerValue;
use App\Models\Position;
use App\Models\Universe;

class DataController extends Controller
{
    public function getPlayers(){
        $players = Player::leftJoin('nflteams', 'nflteams.id', '=', 'players.nflteam_id')
            ->leftJoin('player_position', 'players.id', '=', 'player_position.player_id')
            ->leftJoin('positions', 'positions.id', '=', 'player_position.position_id')
            ->leftJoin('universe', function($join){
                $join->on('universe.player_id', '=', 'players.id');
                if (Auth::user() && Auth::user()->league()){
                    $join->on('universe.league_id', '=', DB::raw(Auth::user()->league()->id));
                }
            })
            ->leftJoin('league_player', function($join){
                $join->on('league_player.player_id', '=', 'players.id');
                if (Auth::user() && Auth::user()->league()){
                    $join->on('league_player.league_id', '=', DB::raw(Auth::user()->league()->id));
                }
            })
            ->leftJoin('player_values', function($join){
                $join->on('player_values.player_id', '=', 'players.id');
                if (Auth::user() && Auth::user()->league()){
                    $join->on('player_values.league_id', '=', DB::raw(Auth::user()->league()->id));
                }
            })
            ->selectRaw('players.*, nflteams.espn_abbr, positions.abbr position, ifnull(universe.active,0) universe, ifnull(league_player.taken,0) taken, player_values.points_above_replacement')
            ->orderBy('universe', 'desc')
            ->orderBy('player_values.points_above_replacement')
            ->get();
        foreach($players as $k=>$player){
            $players[$k]->attributes = $player->attributes();
            $players[$k]->points_above_replacement = floatval($players[$k]->points_above_replacement);
        }
        return $players;
    }
    
    public function getPlayer($slug){
        $player = Player::with('projected_stats', 'positions')->where('slug', $slug)->first();
        if ($player && Auth::user() && Auth::user()->league()){
            $player->in_universe = Universe::where('player_id', $player->id)
                ->where('league_id', Auth::user()->league()->id)
                ->where('active', '1')
                ->count() > 0;
            $player->taken = LeaguePlayer::where('player_id', $player->id)
                ->where('league_id', Auth::user()->league()->id)
                ->where('taken', '1')
                ->count() > 0;
        }else{
            $player->in_universe = false;
        }
        return $player;
    }
    
    public function getTakePlayer($slug){
        $player = Player::where('slug', $slug)->first();
        if ($player && Auth::user() && Auth::user()->league()){
            $league = Auth::user()->league();
            $lp = LeaguePlayer::firstOrCreate(['league_id' => $league->id, 'player_id' => $player->id]);
            $lp->taken = 1;
            $lp->save();
        }
        return 1;
    }
    
    public function getUnTakePlayer($slug){
        $player = Player::where('slug', $slug)->first();
        if ($player && Auth::user() && Auth::user()->league()){
            $league = Auth::user()->league();
            $lp = LeaguePlayer::firstOrCreate(['league_id' => $league->id, 'player_id' => $player->id]);
            $lp->taken = 0;
            $lp->save();
        }
        return 1;
    }
    
    public function getPositions(){
        $positions = Position::all();
        foreach($positions as $p){
            $p->selected = false;
        }
        return $positions;
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
    
    public function getLeague(){
        $league = [];
        if (Auth::user() && Auth::user()->league()){
            $league = Auth::user()->league();
            foreach($league->attributes() as $key=>$val){
                $league->{str_replace('-', '_', $key)} = $val;
            }
        }
        
        return $league;
    }
    
    public function getCalcValues(){
        if (Auth::user() && Auth::user()->league()){
            return Auth::user()->league()->calculateValues();
        }
    }
}
