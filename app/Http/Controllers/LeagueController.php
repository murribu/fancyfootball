<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\League;
use App\Models\Position;

class LeagueController extends Controller
{
    public static $scorings = [
        ['name' => 'Passing Yard', 'slug' => 'passing_yard'],
        ['name' => 'Passing TD', 'slug' => 'passing_td'],
        ['name' => 'Interception', 'slug' => 'passing_interception'],
        ['name' => 'Rushing Yard', 'slug' => 'rushing_yard'],
        ['name' => 'Rushing TD', 'slug' => 'rushing_td'],
        ['name' => 'Receiving Yard', 'slug' => 'receiving_yard'],
        ['name' => 'Receiving TD', 'slug' => 'receiving_td'],
        ['name' => 'Return TD', 'slug' => 'return_td'],
        ['name' => '2-Point Conversion', 'slug' => 'two_point_conversion'],
        ['name' => 'Fumble Lost', 'slug' => 'fumble_lost'],
        ['name' => 'Field Goal 0-39 yards', 'slug' => 'field_goal_1_39'],
        ['name' => 'Field Goal 40-49 yards', 'slug' => 'field_goal_40_49'],
        ['name' => 'Field Goal 50+ yards', 'slug' => 'field_goal_50'],
        ['name' => 'Extra Point', 'slug' => 'extra_point'],
        ['name' => 'Sack', 'slug' => 'sack'],
        ['name' => 'Interception', 'slug' => 'defense_interception'],
        ['name' => 'Fumble Recovery', 'slug' => 'fumble_recovery'],
        ['name' => 'Touchdown', 'slug' => 'defense_touchdown'],
        ['name' => 'Safety', 'slug' => 'safety'],
        ['name' => 'Block Kick', 'slug' => 'block_kick'],
        ['name' => 'Points Allowed: 0', 'slug' => 'points_allowed_0'],
        ['name' => 'Points Allowed: 1-6', 'slug' => 'points_allowed_1_6'],
        ['name' => 'Points Allowed: 7-13', 'slug' => 'points_allowed_7_13'],
        ['name' => 'Points Allowed: 14-20', 'slug' => 'points_allowed_14_20'],
        ['name' => 'Points Allowed: 21-27', 'slug' => 'points_allowed_21_27'],
        ['name' => 'Points Allowed: 28-34', 'slug' => 'points_allowed_28_34'],
        ['name' => 'Points Allowed: 35+', 'slug' => 'points_allowed_35'],
        
    ];
    public function getLeague($slug = null){
        $league = League::where('slug', $slug)
            ->where('user_id', Auth::user()->id)
            ->first();
        return view('league', ['user' => Auth::user(), 'league' => $league, 'scorings' => self::$scorings]);
    }
    
    public function postLeague($slug = null){
        $league = League::where('slug', $slug)
            ->where('user_id', Auth::user()->id)
            ->first();
        if (!$league){
            //new
            $league = new League;
            $league->slug = League::findSlug(Input::get('name'));
        }
        $league->name = Input::get('name');
        $league->user_id = Auth::user()->id;
        $league->save();
        $league->set_attribute('team_count', Input::get('team_count'));
        foreach(self::$scorings as $s){
            $league->set_attribute($s['slug'], Input::get($s['slug']));
        }
        foreach(Position::all() as $p){
            $league->set_attribute('count_'.$p->slug, Input::get($p->slug));
        }
        
        return redirect('/');
    }
    
    public function getSetActive($slug){
        $league = League::where('slug', $slug)
            ->where('user_id', Auth::user()->id)
            ->first();
        if ($league){
            foreach(Auth::user()->leagues as $l){
                $l->active = $l->id == $league->id;
                $l->save();
            }
        }
        
        return redirect('/');
    }
}
