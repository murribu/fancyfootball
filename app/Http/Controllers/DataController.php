<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\Player;

class DataController extends Controller
{
    public function getPlayers(){
        $players = Player::with('nflteam', 'positions')->get();
        foreach($players as $k=>$player){
            $players[$k]->attributes = $player->attributes();
        }
        return $players;
    }
}
