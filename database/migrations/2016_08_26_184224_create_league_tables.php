<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeagueTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('active')->default(0);
            $table->timestamps();
        });
        Schema::create('league_attributes', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('league_attribute_values', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('league_id')->unsigned();
            $table->foreign('league_id')->references('id')->on('leagues');
            $table->integer('league_attribute_id')->unsigned();
            $table->foreign('league_attribute_id')->references('id')->on('league_attributes');
            $table->text('value');
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
        Schema::drop('league_attribute_values');
        Schema::drop('league_attributes');
        Schema::drop('leagues');
    }
}
