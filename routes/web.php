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

if (env('APP_ENV') === 'local') {
    Route::get('auth/login/{id?}', 'Auth\AuthController@loginMe');
}

Route::group(['middleware' => ['auth']], function(){
    Route::get('about', 'HomeController@getAbout');
    Route::get('contact', 'HomeController@getContact');
    
    Route::get('leagues/new', 'LeagueController@getLeague');
    Route::post('leagues/new', 'LeagueController@postLeague');
    Route::get('leagues/{slug}/setactive', 'LeagueController@getSetActive');
    Route::get('leagues/{slug}/edit', 'LeagueController@getLeague');
    Route::post('leagues/{slug}/edit', 'LeagueController@postLeague');
    
    //json
    Route::get('players', 'DataController@getPlayers');
    Route::get('players/{slug}', 'DataController@getPlayer');
    Route::get('players/{slug}/take', 'DataController@getTakePlayer');
    Route::get('players/{slug}/untake', 'DataController@getUnTakePlayer');
    
    Route::get('positions', 'DataController@getPositions');
    
    Route::post('toggle_universe', 'DataController@postToggleUniverse');
    
    Route::get('league', 'DataController@getLeague');
    
    Route::get('calc_values', 'DataController@getCalcValues');
    
});