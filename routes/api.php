<?php

use Illuminate\Http\Request;

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

    }
);

Route::group(
    [
        'middleware' => ['jwt.auth'],
        'prefix' => 'V1'
    ],
    function (){
        //tipo
        Route::get('/tipo', 'TipoController@list');
        Route::post('/tipo', 'TipoController@create');

        // USER MANAGAMENT => Route::get('/user', 'UserController@list');
        Route::post('/user', 'UserController@create');
        Route::get('/managers', 'UserController@managers');

        //Person management
        Route::patch('/person/{id}', 'PersonController@modifyingData');
        Route::delete('/person/{id}', 'PersonController@deleteData');
        Route::get('/personHistory/{uid}', 'PersonController@gaugesHistoryCancellation');

        //readings
        Route::get('/reading', 'LecturaController@getPreviousReading');
        Route::post('/reading', 'LecturaController@setCurrentReadings');

        //lists partners
        Route::get('/partners', 'PartnersController@getPartners');
        Route::get('/partner/{uid}', 'PartnersController@getHistoryCancelled');

        // cancellation
        Route::post('/cancellation', 'CancellationController@setCancellations');
        Route::post('/reprint', 'CancellationController@printCancellation');
        Route::get('/changeCoin', 'CancellationController@exchangeRate');
        Route::get('/historyTransactions', 'CancellationController@history');

        //configure options
        Route::post('/configure', 'ConfigurationController@setConfiguration');
        Route::get('/configure', 'ConfigurationController@getConfigurationState');

        // auth
        Route::post('/auth/refresh', 'TokensController@refresh');
        Route::post('/auth/verify', 'TokensController@verifyValidateToken');
        Route::post('/auth/logout', 'TokensController@logout');
        //***
    }
);
