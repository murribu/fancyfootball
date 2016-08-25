<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nflteams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('location');
            $table->string('name');
            $table->string('espn_abbr');
            $table->timestamps();
        });
        Schema::create('players', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('nflteam_id')->unsigned()->nullable();
            $table->foreign('nflteam_id')->references('id')->on('nflteams');
            $table->string('espn_id')->nullable()->index();
            $table->string('espn_alt_id')->nullable()->index();
            $table->timestamps();
        });
        Schema::create('player_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('player_attribute_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('player_attribute_id')->unsigned();
            $table->foreign('player_attribute_id')->references('id')->on('player_attributes');
            $table->string('value');
            $table->timestamps();
        });
        Schema::create('projected_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('season');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->decimal('passing_attempts', 7, 2)->default(0);
            $table->decimal('passing_completions', 7, 2)->default(0);
            $table->decimal('passing_yards', 7, 2)->default(0);
            $table->decimal('passing_tds', 7, 2)->default(0);
            $table->decimal('passing_ints', 7, 2)->default(0);
            $table->decimal('rushing_attempts', 7, 2)->default(0);
            $table->decimal('rushing_yards', 7, 2)->default(0);
            $table->decimal('rushing_tds', 7, 2)->default(0);
            $table->decimal('receiving_targets', 7, 2)->default(0);
            $table->decimal('receptions', 7, 2)->default(0);
            $table->decimal('receiving_yards', 7, 2)->default(0);
            $table->decimal('receiving_tds', 7, 2)->default(0);
            $table->decimal('fumbles', 7, 2)->default(0);
            $table->decimal('defense_sacks', 7, 2)->default(0);
            $table->decimal('defense_ints', 7, 2)->default(0);
            $table->decimal('defense_fumble_recoveries', 7, 2)->default(0);
            $table->decimal('defense_tds', 7, 2)->default(0);
            $table->decimal('defense_points_against', 7, 2)->default(0);
            $table->decimal('defense_yards_against', 7, 2)->default(0);
            $table->decimal('fg_1_39_attempted', 7, 2)->default(0);
            $table->decimal('fg_1_39_made', 7, 2)->default(0);
            $table->decimal('fg_40_49_attempted', 7, 2)->default(0);
            $table->decimal('fg_40_49_made', 7, 2)->default(0);
            $table->decimal('fg_50_attempted', 7, 2)->default(0);
            $table->decimal('fg_50_made', 7, 2)->default(0);
            $table->decimal('extra_points_attempted', 7, 2)->default(0);
            $table->decimal('extra_points_made', 7, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('abbr');
            $table->string('name');
            $table->enum('type', ['offense', 'defense', 'kicker']);
            $table->timestamps();
        });
        Schema::create('player_position', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('position_id')->unsigned();
            $table->foreign('position_id')->references('id')->on('positions');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('player_position');
        Schema::drop('positions');
        Schema::drop('projected_stats');
        Schema::drop('player_attribute_values');
        Schema::drop('player_attributes');
        Schema::drop('players');
        Schema::drop('nflteams');
    }
}
