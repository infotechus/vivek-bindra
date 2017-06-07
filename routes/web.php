<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$api = app('Dingo\Api\Routing\Router');

//Route::group(['namespace' => 'App\Http\Controllers',], function ($api) {
//    Route::post('/signUp', function(){
//        //['as' => 'user.index', 'uses' => 'UserController@signUp']
//        return 'done';
//    });
//    
//});

$api->version('v1', function ($api) {
    
    
    $api->group(['namespace' => 'App\Http\Controllers'], function ($api) {
        
         $api->post('/signUp', ['as' => 'user.index', 'uses' => 'UserController@signUp']);
         
         $api->get('/user/activation/{tokenId}', ['as' => 'user.index', 'uses' => 'UserController@userActivation']);
         
         $api->post('/signUp', ['as' => 'user.index', 'uses' => 'AuthController@authenticate']);
    });
    
    // For login purpose
    $api->group(['namespace' => 'Auth'], function ($api) {
        
         $api->post('/authenticate', ['as' => 'user.authenticate', 'uses' => 'AuthController@authenticate']);
         
    });
    
   
});

//Route::get('/', function () {
//    return view('welcome');
//});
