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
        // DB::enableQueryLog();
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
            ->selectRaw('players.id, players.first_name, players.last_name, players.slug, nflteams.espn_abbr, positions.abbr position, ifnull(universe.active,0) universe, ifnull(league_player.taken,0) taken, player_values.points_above_replacement')
            ->orderBy('points_above_replacement', 'desc')
            ->limit(350)
            ->get();
        // dd(DB::getQueryLog());
        $rank = 1;
        $total_taken = 0;
        $my_pick = -1;
        $taken_so_far = 0;
        $projected_pick = 0;
        if (Auth::user() && Auth::user()->league()){
            $league = Auth::user()->league();
            $my_pick = intval($league->attribute('my_pick'));
            $team_count = intval($league->attribute('team_count'));
            $total_taken = LeaguePlayer::where('league_id', $league->id)->where('taken', 1)->count();
        }
        foreach($players as $k=>$player){
            $players[$k]->attributes = $player->attributes();
            $players[$k]->points_above_replacement = floatval($players[$k]->points_above_replacement);
            
            if ($players[$k]->taken == 1){
                $taken_so_far++;
            }
            $projected_pick = $total_taken - $taken_so_far + $rank;
            $players[$k]->projected_pick = $projected_pick;
            $players[$k]->my_pick = $projected_pick % ($team_count*2) == $my_pick || ((($team_count*2)+1) - $projected_pick) % ($team_count*2) == $my_pick;
            $players[$k]->my_rank = $rank++;
            
            
            unset($players[$k]->player_attributes_values);
            unset($players[$k]->id);
        }
        return $players;
    }
    
    public function getPlayer($slug){
        $player = Player::with('projected_stats')->where('slug', $slug)->first();
        if ($player && Auth::user() && Auth::user()->league()){
            $player->in_universe = Universe::where('player_id', $player->id)
                ->where('league_id', Auth::user()->league()->id)
                ->where('active', '1')
                ->count() > 0;
            $player->taken = LeaguePlayer::where('player_id', $player->id)
                ->where('league_id', Auth::user()->league()->id)
                ->where('taken', '1')
                ->count() > 0;
            $player->position_type = count($player->positions) > 0 ? $player->positions[0]->type : '';
            $player->outlook = $player->attribute('espn_outlook');
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
        $player = Player::with('projected_stats')->where('slug', Input::get('player'))->first();
        if ($player && Auth::user() && Auth::user()->league()){
            $u = Universe::firstOrCreate(['player_id' => $player->id, 'league_id' => Auth::user()->league()->id]);
            
            $u->active = !$u->active;
            $u->save();
            $player->in_universe = $u->active;
            $player->taken = LeaguePlayer::where('player_id', $player->id)
                ->where('league_id', Auth::user()->league()->id)
                ->where('taken', '1')
                ->count() > 0;
            $player->position_type = count($player->positions) > 0 ? $player->positions[0]->type : '';
            $player->outlook = $player->attribute('espn_outlook');
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
            $league->count_qb = intval($league->attribute('team_count')) * intval($league->count_qb);
            $league->count_k = intval($league->attribute('team_count')) * intval($league->count_k);
            $league->count_wr = intval($league->attribute('team_count')) * intval($league->count_wr);
            $league->count_te = intval($league->attribute('team_count')) * intval($league->count_te);
            $league->count_d_st = intval($league->attribute('team_count')) * intval($league->count_d_st);
            $league->count_rb = intval($league->attribute('team_count')) * intval($league->count_rb);
            if (intval($league->attributes('count_bench') > 0)){
                $bench = intval($league->attribute('count_bench')) * intval($league->attribute('team_count'));
                $qb_ratio = 1/6;
                $wr_ratio = 4/9;
                $rb_ratio = 5/18;
                $te_ratio = 1/12;
                $d_ratio = 1/36;
                $league->count_qb += intval($bench * $qb_ratio);
                $league->count_wr += intval($bench * $wr_ratio);
                $league->count_te += intval($bench * $te_ratio);
                $league->count_d_st += intval($bench * $d_ratio);
                $league->count_rb += ($bench - intval($bench * $qb_ratio) - intval($bench * $wr_ratio) - intval($bench * $te_ratio) - intval($bench * $d_ratio));
            }
            unset($league->league_attributes_values);
        }
        
        return $league;
    }
    
    public function getCalcValues(){
        if (Auth::user() && Auth::user()->league()){
            return Auth::user()->league()->calculateValues();
        }
    }
}
