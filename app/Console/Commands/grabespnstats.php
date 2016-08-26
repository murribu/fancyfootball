<?php

namespace App\Console\Commands;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Models\Player;
use App\Models\ProjectedStat;

class grabespnstats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grabespnstats {--force : Whether the job should be forced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grab ESPN Stats for one player';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $force = $this->option('force');
        $frequency = 2; //every $frequency minutes
        if (mt_rand(0,$frequency) == 1 || $force){
            $player = Player::leftJoin('projected_stats', 'projected_stats.player_id', '=', 'players.id')
                ->select('players.id', 'players.slug', 'players.first_name', 'players.last_name', 'espn_alt_id')
                // ->where('players.id', 77)
                // ->whereRaw('players.id in (select player_id from player_position where position_id = (select id from positions where slug = ?))', array('k'))
                ->orderBy('projected_stats.created_at')
                ->first();
            if ($player->positions[0]->slug == 'd-st'){
                $search = $player->first_name;
            }else{
                $search = $player->last_name;
            }
            $run_from_local = false;
            if ($run_from_local){
                $str = Storage::get('data/'.$player->slug.'.html');
            }else{
                $url = "http://games.espn.com/ffl/tools/projections?display=alt&avail=-1&search=".urlencode($search);
                $str = file_get_contents($url);
                Storage::put('data/'.$player->slug.'.html', $str);
            }
            $DOM = new \DOMDocument;
            //The following line make DOMDocument ignore errors. This is needed because it's not prepared for HTML5
            libxml_use_internal_errors(true);
            if ($DOM->loadHTML($str)) {
                libxml_clear_errors();
                $tables = $DOM->getElementsByTagName('table');
                foreach($tables as $table){
                    $as = $table->getElementsByTagName('a');
                    if ($as->length > 0){
                        $pid = $as->item(0)->getAttribute('playerid');
                        // dd($as->item(0)->nodeValue);
                        if ($pid == $player->espn_alt_id){
                            $stat = ProjectedStat::where('player_id', $player->id)
                                ->where('season', 2016)
                                ->first();
                            if (!$stat){
                                $stat = new ProjectedStat;
                                $stat->player_id = $player->id;
                                $stat->season = 2016;
                                $stat->save();
                            }
                            $tr = $table->getElementsByTagName('tr')->item(2);
                            $tds = $tr->getElementsByTagName('td');
                            switch($player->positions[0]->slug){
                                case 'wr':
                                case 'te':
                                    $stat->receiving_targets = $tds->item(2)->nodeValue;
                                    $stat->receptions = $tds->item(3)->nodeValue;
                                    $stat->receiving_yards = $tds->item(4)->nodeValue;
                                    $stat->receiving_tds = $tds->item(6)->nodeValue;
                                    $stat->rushing_attempts = $tds->item(7)->nodeValue;
                                    $stat->rushing_yards = $tds->item(8)->nodeValue;
                                    $stat->rushing_tds = $tds->item(9)->nodeValue;
                                    $stat->save();
                                    break;
                                case 'qb':
                                    $ca = $tds->item(2)->nodeValue;
                                    $stat->passing_attempts = trim(substr($ca, 0, strpos($ca,'/')));
                                    $stat->passing_completions = trim(substr($ca, strpos($ca,'/')+1, 999));
                                    $stat->passing_yards = $tds->item(3)->nodeValue;
                                    $stat->passing_tds = $tds->item(4)->nodeValue;
                                    $stat->passing_ints = $tds->item(5)->nodeValue;
                                    $stat->rushing_attempts = $tds->item(6)->nodeValue;
                                    $stat->rushing_yards = $tds->item(7)->nodeValue;
                                    $stat->rushing_tds = $tds->item(8)->nodeValue;
                                    $stat->save();
                                    break;
                                case 'rb':
                                    $stat->rushing_attempts = $tds->item(2)->nodeValue;
                                    $stat->rushing_yards = $tds->item(3)->nodeValue;
                                    $stat->rushing_tds = $tds->item(5)->nodeValue;
                                    $stat->receptions = $tds->item(6)->nodeValue;
                                    $stat->receiving_yards = $tds->item(7)->nodeValue;
                                    $stat->receiving_tds = $tds->item(8)->nodeValue;
                                    $stat->save();
                                    break;
                                case 'd-st':
                                    $stat->defense_sacks = $tds->item(2)->nodeValue;
                                    $stat->defense_ints = $tds->item(3)->nodeValue;
                                    $stat->defense_fumble_recoveries = $tds->item(4)->nodeValue;
                                    $stat->defense_tds = $tds->item(5)->nodeValue;
                                    $stat->defense_points_against = $tds->item(6)->nodeValue;
                                    $stat->defense_yards_against = $tds->item(7)->nodeValue;
                                    $stat->save();
                                    dd($stat);
                                    break;
                                case 'k':
                                    $node = $tds->item(2)->nodeValue;
                                    $stat->fg_1_39_attempted = trim(substr($node, 0, strpos($node,'/')));
                                    $stat->fg_1_39_made = trim(substr($node, strpos($node,'/') + 1, 999));
                                    $node = $tds->item(3)->nodeValue;
                                    $stat->fg_40_49_attempted = trim(substr($node, 0, strpos($node,'/')));
                                    $stat->fg_40_49_made = trim(substr($node, strpos($node,'/') + 1, 999));
                                    $stat->fg_50_attempted = $tds->item(4)->nodeValue;
                                    $stat->fg_50_made = $tds->item(4)->nodeValue;
                                    $node = $tds->item(6)->nodeValue;
                                    $stat->extra_points_attempted = trim(substr($node, 0, strpos($node,'/')));
                                    $stat->extra_points_made = trim(substr($node, strpos($node,'/') + 1, 999));
                                    $stat->save();
                                    break;
                                default:
                                    echo 'Bad position';
                                    dd($player);
                                    break;
                            }
                            if ($outlooktr = $table->getElementsByTagName('tr')->item(3)){
                                $player->set_attribute('espn_outlook', $outlooktr->getElementsByTagName('td')->item(0)->textContent);
                            }
                        }
                    }
                }
            }
            $this->info('Grabbed stats for '.$player->first_name.' '. $player->last_name);
        }
    }
}
