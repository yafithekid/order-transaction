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

Route::group(['prefix'=>'api/v1/transactions'],function(){
    Route::post('/add_product',['uses'=>'TransactionController@postAddProduct']);
    Route::post('/submit',['uses'=>'TransactionController@postSubmit']);
    Route::post('/apply_coupon',['uses'=>'TransactionController@postApplyCoupoon']);
    Route::post('/{transaction_id}/send_payment_proof',['uses'=>'TransactionController@postSendPaymentProof']);
    Route::post('/{transaction_id}/reject',['uses'=>'TransactionController@postReject']);
    Route::post('/{transaction_id}/prepare_shipment',['uses'=>'TransactionController@postPrepareShipment']);
    Route::post('/{transaction_id}/shipped',['uses'=>'TransactionController@postShipped']);
    Route::post('/{transaction_id}/received',['uses'=>'TransactionController@postReceived']);
    Route::get('/track_shipment',['uses'=>'TransactionController@getTrackShipment']);
    Route::get('/{transaction_id}',['uses'=>'TransactionController@getRead']);
});