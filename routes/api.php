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
        Route::get('/updatePass/{uid}', 'UserController@updatePass');

        //Person management
        Route::patch('/person/{id}', 'PersonController@modifyingData');
        Route::delete('/person/{id}', 'PersonController@deleteData');
        Route::get('/personHistory/{uid}', 'PersonController@gaugesHistoryCancellation');

        //Reports partners
        Route::post('/reports', 'ReportsController@partnerTransactions');

        //readings
        Route::get('/reading', 'LecturaController@getPreviousReading');
        Route::post('/reading', 'LecturaController@setCurrentReadings');
        Route::patch('/reading/{id}', 'LecturaController@updateReading');
        Route::get('/months', 'LecturaController@monthsReadings');
        Route::post('/readingMonth', 'LecturaController@monthReading');

        //lists partners
        Route::get('/partners', 'PartnersController@getPartners');
        Route::get('/partner/{uid}', 'PartnersController@getHistoryCancelled');
        Route::get('/partnerData/{uid}', 'PartnersController@getPartner');
        Route::patch('/partner/{uid}', 'PartnersController@updatePartner');

        // cancellation
        Route::post('/cancellation', 'CancellationController@setCancellations');
        Route::get('/cancel/{key}', 'CancellationController@cancelTransaction');
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
