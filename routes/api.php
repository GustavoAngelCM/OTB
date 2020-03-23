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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(
    [
        'middleware' => [],
        'prefix' => 'V1'
    ],
    function (){
        // JWT AUTH
        Route::post('/auth/login', 'TokensController@login');
        Route::post('/auth/refresh', 'TokensController@refreshToken');
        Route::get('/auth/logout', 'TokensController@logout');
    }
);

Route::group(
    [
        'middleware' => [],
        'prefix' => 'NOT-SECURE'
    ],
    function (){
        Route::get('/tipo', 'TipoController@list');
    }
);

Route::group(
    [
        'middleware' => ['jwt.auth'],
        'prefix' => 'V1'
    ],
    function (){
        Route::get('/tipo', 'TipoController@list');
        Route::post('/tipo', 'TipoController@create');

        Route::get('/user', 'UserController@list');
        Route::post('/user', 'UserController@create');

        //lecturas
        Route::get('/reading', 'LecturaController@getPreviousReading');
    }
);
