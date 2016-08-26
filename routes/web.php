<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'HomeController@getHome');

Route::get('auth/facebook', 'Auth\AuthController@redirectToProvider');
Route::get('auth/facebook/callback', 'Auth\AuthController@handleProviderCallback');

Route::get('auth/me', 'Auth\AuthController@getMe');
Route::get('auth/logout', 'Auth\AuthController@logout');

// Route::get('auth/login/{id?}', 'Auth\AuthController@loginMe');

Route::group(['middleware' => ['auth']], function(){
    Route::get('about', 'HomeController@getAbout');
    Route::get('contact', 'HomeController@getContact');
    
    Route::get('players', 'DataController@getPlayers');
    
});