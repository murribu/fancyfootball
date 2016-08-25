<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Models\Nflteam;
use App\Models\Player;
use App\Models\Position;

class PlayerSeeder extends Seeder{
    
    public function run(){
        $teams = array(
            array(
                'name' => 'Cardinals',
                'location' => 'Arizona',
                'espn_abbr' => 'Ari',
            ),
            array(
                'name' => 'Falcons',
                'location' => 'Atlanta',
                'espn_abbr' => 'Atl',
            ),
            array(
                'name' => 'Ravens',
                'location' => 'Baltimore',
                'espn_abbr' => 'Bal',
            ),
            array(
                'name' => 'Bills',
                'location' => 'Buffalo',
                'espn_abbr' => 'Buf',
            ),
            array(
                'name' => 'Panthers',
                'location' => 'Carolina',
                'espn_abbr' => 'Car',
            ),
            array(
                'name' => 'Bears',
                'location' => 'Chicago',
                'espn_abbr' => 'Chi',
            ),
            array(
                'name' => 'Bengals',
                'location' => 'Cincinnati',
                'espn_abbr' => 'Cin',
            ),
            array(
                'name' => 'Browns',
                'location' => 'Cleveland',
                'espn_abbr' => 'Cle',
            ),
            array(
                'name' => 'Cowboys',
                'location' => 'Dallas',
                'espn_abbr' => 'Dal',
            ),
            array(
                'name' => 'Broncos',
                'location' => 'Denver',
                'espn_abbr' => 'Den',
            ),
            array(
                'name' => 'Lions',
                'location' => 'Detroit',
                'espn_abbr' => 'Det',
            ),
            array(
                'name' => 'Packers',
                'location' => 'Green Bay',
                'espn_abbr' => 'GB',
            ),
            array(
                'name' => 'Texans',
                'location' => 'Houston',
                'espn_abbr' => 'Hou',
            ),
            array(
                'name' => 'Colts',
                'location' => 'Indianapolis',
                'espn_abbr' => 'Ind',
            ),
            array(
                'name' => 'Jaguars',
                'location' => 'Jacksonville',
                'espn_abbr' => 'Jax',
            ),
            array(
                'name' => 'Chiefs',
                'location' => 'Kansas City',
                'espn_abbr' => 'KC',
            ),
            array(
                'name' => 'Rams',
                'location' => 'Los Angeles',
                'espn_abbr' => 'LA',
            ),
            array(
                'name' => 'Dolphins',
                'location' => 'Miami',
                'espn_abbr' => 'Mia',
            ),
            array(
                'name' => 'Vikings',
                'location' => 'Minnesota',
                'espn_abbr' => 'Min',
            ),
            array(
                'name' => 'Giants',
                'location' => 'New York',
                'espn_abbr' => 'NYG',
            ),
            array(
                'name' => 'Jets',
                'location' => 'New York',
                'espn_abbr' => 'NYJ',
            ),
            array(
                'name' => 'Patriots',
                'location' => 'New England',
                'espn_abbr' => 'NE',
            ),
            array(
                'name' => 'Saints',
                'location' => 'New Orleans',
                'espn_abbr' => 'NO',
            ),
            array(
                'name' => 'Raiders',
                'location' => 'Oakland',
                'espn_abbr' => 'Oak',
            ),
            array(
                'name' => 'Eagles',
                'location' => 'Philadelphia',
                'espn_abbr' => 'Phi',
            ),
            array(
                'name' => 'Steelers',
                'location' => 'Pittsburgh',
                'espn_abbr' => 'Pit',
            ),
            array(
                'name' => '49ers',
                'location' => 'San Francisco',
                'espn_abbr' => 'SF',
            ),
            array(
                'name' => 'Chargers',
                'location' => 'San Diego',
                'espn_abbr' => 'SD',
            ),
            array(
                'name' => 'Seahawks',
                'location' => 'Seattle',
                'espn_abbr' => 'Sea',
            ),
            array(
                'name' => 'Buccaneers',
                'location' => 'Tampa Bay',
                'espn_abbr' => 'TB',
            ),
            array(
                'name' => 'Titans',
                'location' => 'Tennessee',
                'espn_abbr' => 'Ten',
            ),
            array(
                'name' => 'Redskins',
                'location' => 'Washington',
                'espn_abbr' => 'Wsh',
            ),
        );
        
        foreach($teams as $team){
            $t = Nflteam::where('name', $team['name'])->first();
            if (!$t){
                $t = new Nflteam;
                $t->name = $team['name'];
                $t->location = $team['location'];
                $t->espn_abbr = $team['espn_abbr'];
                $t->slug = Nflteam::findSlug($team['name']);
                $t->save();
            }
        }
        
        $positions = array(
            array(
                'abbr' => 'QB',
                'name' => 'Quarter Back',
                'type' => 'offense',
            ),
            array(
                'abbr' => 'RB',
                'name' => 'Running Back',
                'type' => 'offense',
            ),
            array(
                'abbr' => 'WR',
                'name' => 'Wide Receiver',
                'type' => 'offense',
            ),
            array(
                'abbr' => 'TE',
                'name' => 'Tight End',
                'type' => 'offense',
            ),
            array(
                'abbr' => 'D-ST',
                'name' => 'Defense and Special Teams',
                'type' => 'defense',
            ),
            array(
                'abbr' => 'K',
                'name' => 'Kicker',
                'type' => 'kicker',
            ),
        );
        
        foreach($positions as $position){
            $p = Position::where('abbr', $position['abbr'])->first();
            if (!$p){
                $p = new Position;
                $p->abbr = $position['abbr'];
                $p->name = $position['name'];
                $p->type = $position['type'];
                $p->slug = Position::findSlug($p->abbr);
                $p->save();
            }
        }
        
        $contents = File::get('storage/app/data/nfl.html');
        $DOM = new \DOMDocument;
        $DOM->loadHTML($contents);
        foreach($DOM->getElementsByTagName('tbody') as $tbody){
            foreach($tbody->getElementsByTagName('tr') as $tr){
                $position_abbrs = collect($positions)->pluck('abbr');
                $team_abbrs = collect($teams)->pluck('espn_abbr');
                if (substr($tr->getAttribute('class'),0,12) == 'pncPlayerRow'){
                    //it's a player
                    $tds = $tr->getElementsByTagName('td');
                    $espn_alt_id = $tds->item(1)->getElementsByTagName('a')->item(0)->getAttribute('playerid');
                    $p = Player::where('espn_alt_id', $espn_alt_id)->first();
                    $team_position_str = substr($tds->item(1)->nodeValue, strrpos($tds->item(1)->nodeValue, ',') + 2, 999);
                    if (!$p && substr($team_position_str,0,2) != 'FA'){
                        $p = new Player;
                        $p->espn_alt_id = $espn_alt_id;
                        $name = $tds->item(1)->getElementsByTagName('a')->item(0)->nodeValue;
                        $p->first_name = substr($name, 0, strpos($name, ' '));
                        $p->last_name = substr($name, strpos($name, ' ') + 1, 999);
                        if ($p->last_name == 'D/ST'){
                            $position = Position::where('abbr', 'D-ST')->first();
                            $team = Nflteam::where('name', $p->first_name)->first();
                            if (!$team || !$team){
                                echo "Bad team or position for ";
                                dd($p);
                            }
                        }else{
                            $team = false;
                            for($i = 1; $i < strlen($team_position_str); $i++){
                                if ($team_abbrs->contains(substr($team_position_str,0,$i))){
                                    // var_dump(substr($team_position_str,0,$i));
                                    $team = Nflteam::where('espn_abbr', substr($team_position_str,0,$i))->first();
                                    // var_dump($team);
                                    break;
                                }
                            }
                            if (!$team){
                                dd('Could not find a team for this string: '.$team_position_str);
                            }
                            $position = false;
                            for($j = $i; $j <= strlen($team_position_str); $j++){
                                // var_dump(substr($team_position_str,$i,$j-$i));
                                if ($position_abbrs->contains(substr($team_position_str,$i,$j-$i))){
                                    $position = Position::where('abbr', substr($team_position_str,$i,$j-$i))->first();
                                }
                            }
                            if (!$position){
                                dd('Could not find a position for this string: '.$team_position_str);
                            }
                        }
                        $p->nflteam_id = $team->id;
                        $p->slug = Player::findSlug($name);
                        $p->save();
                        $p->positions()->sync([$position->id]);
                        // dd($p);
                    }
                }
            }
        }
    }
}