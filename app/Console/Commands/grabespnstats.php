<?php

namespace App\Console\Commands;

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
        $frequency = 60; //once an hour
        if (mt_rand(0,$frequency) == 1 || $force){
            $player = Player::leftJoin('projected_stats', 'projected_stats.player_id', '=', 'players.id')
                ->select('players.id', 'players.slug', 'espn_alt_id')
                ->where('players.id', 1)
                ->orderBy('projected_stats.created_at')
                ->first();

            $url = "http://games.espn.com/ffl/tools/projections?display=alt&avail=-1&search=".urlencode($player->last_name);
            $str = file_get_contents($url);
            Storage::put('data/'.$player->slug.'.html', $str);
            // $str = Storage::get('data/brown.html');
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
                                case 'rb':
                                case 'd-st':
                                case 'k':
                                default:
                                    echo 'Bad position';
                                    dd($player);
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }
}
