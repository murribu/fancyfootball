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
        $frequency = 5; //every $frequency minutes
        if (mt_rand(0,$frequency) == 1 || $force){
            $player = Player::leftJoin('projected_stats', 'projected_stats.player_id', '=', 'players.id')
                ->select('players.id', 'players.slug', 'players.first_name', 'players.last_name', 'espn_alt_id')
                // ->where('players.id', 272)
                // ->whereRaw('players.id in (select player_id from player_position where position_id = (select id from positions where slug = ?))', array('k'))
        		// Only grab players who have an espn_rank that has been updated in 2017
                ->whereIn('players.id', function($query){
                    $query->select('player_id')
                        ->from('player_attribute_values')
                        ->where('player_attribute_id', '2')
                        ->where('updated_at', '>', '2017-1-1');
                })
                ->orderBy('projected_stats.updated_at')
                ->first();
            if ($player->positions[0]->slug == 'd-st'){
                // TODO: refactor this - it has to be manually changed to get different strata of defenses
                $search = "&slotCategoryId=16&search=d/st&startIndex=15";
            }else{
                $search = urlencode($player->last_name);
            }
            $run_from_local = false;
            if ($run_from_local){
                $str = Storage::get('data/'.$player->slug.'.html');
            }else{
                $url = "http://games.espn.com/ffl/tools/projections?display=alt&avail=-1&search=".$search;
                $str = file_get_contents($url);
                @Storage::put('data/'.$player->slug.'.html', $str);
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
                        $stat = ProjectedStat::where('player_id', $player->id)
                            ->where('season', 2017)
                            ->first();
                        if (!$stat){
                            $stat = new ProjectedStat;
                            $stat->player_id = $player->id;
                            $stat->season = 2017;
                            $stat->save();
                        }
                        if ($pid == $player->espn_alt_id){
                            $tr = $table->getElementsByTagName('tr')->item(2);
                            $tds = $tr->getElementsByTagName('td');
                            switch($player->positions[0]->slug){
                                case 'wr':
                                case 'te':
                                    $stat->receiving_targets = floatval($tds->item(2)->nodeValue);
                                    $stat->receptions = floatval($tds->item(3)->nodeValue);
                                    $stat->receiving_yards = floatval($tds->item(4)->nodeValue);
                                    $stat->receiving_tds = floatval($tds->item(6)->nodeValue);
                                    $stat->rushing_attempts = floatval($tds->item(7)->nodeValue);
                                    $stat->rushing_yards = floatval($tds->item(8)->nodeValue);
                                    $stat->rushing_tds = floatval($tds->item(9)->nodeValue);
                                    $stat->save();
                                    break;
                                case 'qb':
                                    $ca = $tds->item(2)->nodeValue;
                                    $stat->passing_attempts = floatval(trim(substr($ca, 0, strpos($ca,'/'))));
                                    $stat->passing_completions = floatval(trim(substr($ca, strpos($ca,'/')+1, 999)));
                                    $stat->passing_yards = floatval($tds->item(3)->nodeValue);
                                    $stat->passing_tds = floatval($tds->item(4)->nodeValue);
                                    $stat->passing_ints = floatval($tds->item(5)->nodeValue);
                                    $stat->rushing_attempts = floatval($tds->item(6)->nodeValue);
                                    $stat->rushing_yards = floatval($tds->item(7)->nodeValue);
                                    $stat->rushing_tds = floatval($tds->item(8)->nodeValue);
                                    $stat->save();
                                    break;
                                case 'rb':
                                    $stat->rushing_attempts = floatval($tds->item(2)->nodeValue);
                                    $stat->rushing_yards = floatval($tds->item(3)->nodeValue);
                                    $stat->rushing_tds = floatval($tds->item(5)->nodeValue);
                                    $stat->receptions = floatval($tds->item(6)->nodeValue);
                                    $stat->receiving_yards = floatval($tds->item(7)->nodeValue);
                                    $stat->receiving_tds = floatval($tds->item(8)->nodeValue);
                                    $stat->save();
                                    break;
                                case 'd-st':
                                    $stat->defense_sacks = floatval($tds->item(2)->nodeValue);
                                    $stat->defense_ints = floatval($tds->item(3)->nodeValue);
                                    $stat->defense_fumble_recoveries = floatval($tds->item(4)->nodeValue);
                                    $stat->defense_tds = floatval($tds->item(5)->nodeValue);
                                    $stat->defense_points_against = floatval($tds->item(6)->nodeValue);
                                    $stat->defense_yards_against = floatval($tds->item(7)->nodeValue);
                                    $stat->save();
                                    break;
                                case 'k':
                                    $node = $tds->item(2)->nodeValue;
                                    $stat->fg_1_39_made = floatval(trim(substr($node, 0, strpos($node,'/'))));
                                    $stat->fg_1_39_attempted = floatval(trim(substr($node, strpos($node,'/') + 1, 999)));
                                    $node = $tds->item(3)->nodeValue;
                                    $stat->fg_40_49_made = floatval(trim(substr($node, 0, strpos($node,'/'))));
                                    $stat->fg_40_49_attempted = floatval(trim(substr($node, strpos($node,'/') + 1, 999)));
                                    $stat->fg_50_made = floatval($tds->item(4)->nodeValue);
                                    $stat->fg_50_attempted = floatval($tds->item(4)->nodeValue);
                                    $node = $tds->item(6)->nodeValue;
                                    $stat->extra_points_made = floatval(trim(substr($node, 0, strpos($node,'/'))));
                                    $stat->extra_points_attempted = floatval(trim(substr($node, strpos($node,'/') + 1, 999)));
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
                        $stat->touch();
                    }
                }
            }
            $this->info('Grabbed stats for '.$player->first_name.' '. $player->last_name);
        }
    }
}
