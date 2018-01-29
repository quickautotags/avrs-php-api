<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/testFeeCalculator','Controller@testFeeCalculator');
Route::get('/viewTestRecords','Controller@viewTestRecords');
Route::get('/exampleRenewRegistration','Controller@exampleRenewRegistrationFull');
Route::get('/exampleRenewRegistrationFirst','Controller@exampleRenewRegistrationFirst');
Route::get('/exampleRenewRegistrationRest','Controller@exampleRenewRegistrationRest');
//Route::post('/campaignPerformanceReport', 'Controller@campaignPerformanceReport');

//I will only be doing “renewals/replacement credentials” – and my fee will be $20 - Uni