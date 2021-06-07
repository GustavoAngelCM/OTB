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
        Route::get('/ads', 'AdController@listAds');
        Route::get('/pdf', 'PdfReportDownload@pdfTest');
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

        // USER MANAGEMENT => Route::get('/user', 'UserController@list');r
        Route::post('/user', 'UserController@create');
        Route::get('/managers', 'UserController@managers');
        Route::get('/updatePass/{uid}', 'UserController@updatePass')->middleware('admin');

        //Person management
        Route::patch('/person/{id}', 'PersonController@modifyingData');
        Route::delete('/person/{id}', 'PersonController@deleteData');
        Route::get('/personHistory/{uid}', 'PersonController@gaugesHistoryCancellation');

        //Reports partners
        Route::post('/reports', 'ReportsController@partnerTransactions')->middleware('admin');

        //readings
        Route::get('/reading', 'LecturaController@getPreviousReading');
        Route::post('/reading', 'LecturaController@setCurrentReadings')->middleware('admin');
        Route::patch('/reading/{id}', 'LecturaController@updateReading');
        Route::get('/months', 'LecturaController@monthsReadings');
        Route::get('/monthsUntilNow', 'LecturaController@monthsUntilNow');
        Route::post('/readingMonth', 'LecturaController@monthReading');

        //lists partners
        Route::get('/partners', 'PartnersController@getPartners')->middleware('admin');
        Route::get('/partner/{uid}', 'PartnersController@getHistoryCancelled');
        Route::get('/partnerData/{uid}', 'PartnersController@getPartner');
        Route::patch('/partner/{uid}', 'PartnersController@updatePartner');
        Route::get('/partnerExceptFor/{uid}', 'PartnersController@getPartnersExcept');
        Route::post('/transferOfShareToAnotherPartner', 'PartnersController@meterTransfer');

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

        // events and assists
        Route::post('/event', 'EventController@createEvent');
        Route::get('/event', 'EventController@events');
        Route::put('/event/{id}', 'EventController@updateEvent');
        Route::delete('/event/{id}', 'EventController@deleteEvent');
        Route::post('/attendanceAtEvents/{id}', 'EventController@assistsEvent');
        Route::post('/cancellationForAbsence', 'EventController@cancellationRecord');
        Route::get('/event/{event}/{type}', 'EventController@getListAssists');

        Route::get('/gauge/{uid}', 'GaugeController@listOfMetersByPartner')->middleware('admin');
        Route::patch('/gauge/{key}', 'GaugeController@updateGauge')->middleware('admin');
        Route::delete('/gauge/{number}', 'GaugeController@deleteGauge')->middleware('admin');
        Route::put('/gauge/{uid}', 'GaugeController@createGauge')->middleware('admin');

        Route::post('/ad', 'AdController@createAd')->middleware('admin');
        Route::put('/ad/{id}', 'AdController@editAd')->middleware('admin');
        Route::delete('/ad/{id}', 'AdController@deleteAd')->middleware('admin');

        Route::patch('/user/{id}', 'UserController@editUser')->middleware('partnerAndManager');

        Route::get('/transactions', 'TransactionController@getTransactions')->middleware('admin');
        Route::post('/transactionPayment', 'TransactionController@paymentTransaction')->middleware('admin');
    }
);
