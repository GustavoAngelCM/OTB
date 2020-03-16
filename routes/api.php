<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(
    [
        'middleware' => [],
        'prefix' => 'V1'
    ],
    function (){
        Route::post('/auth/login', 'TokensController@login');
    }
);



Route::get('/tipo', 'TipoController@list');
Route::post('/tipo', 'TipoController@create');

Route::get('/user', 'UserController@list');
Route::post('/user', 'UserController@create');
