<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\Player;

class DataController extends Controller
{
    public function getPlayers(){
        $players = Player::leftJoin('nflteams', 'nflteams.id', '=', 'players.nflteam_id')
            ->leftJoin('player_position', 'players.id', '=', 'player_position.player_id')
            ->leftJoin('positions', 'positions.id', '=', 'player_position.position_id')
            ->selectRaw('players.*, nflteams.espn_abbr, positions.abbr position')
            ->get();
        foreach($players as $k=>$player){
            $players[$k]->attributes = $player->attributes();
        }
        return $players;
    }
}
