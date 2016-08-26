<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('avatar');
            $table->string('facebook_id')->unique();
            $table->timestamps();
        });
        Schema::create('facebook_tokens', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('facebook_user_id')->unsigned();
            $table->foreign('facebook_user_id')->references('id')->on('facebook_users');
            $table->string('token');
            $table->string('refreshToken')->nullable();
            $table->integer('expiresIn')->unsigned();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('facebook_user_id')->unsigned()->nullable();
            $table->foreign('facebook_user_id')->references('id')->on('facebook_users');
            $table->datetime('last_login')->nullable();
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
        Schema::drop('users');
        Schema::drop('facebook_tokens');
        Schema::drop('facebook_users');
    }
}
