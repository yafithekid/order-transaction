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

Route::group(['prefix'=>'api/v1','namespace'=>'Api\V1'],function(){
    Route::group(['prefix'=>'images'],function(){
        Route::post('/upload',['uses'=>'ImageController@postUpload']);
    });
    Route::group(['prefix'=>'transactions'],function(){
        Route::post('/add_product',['uses'=>'TransactionController@postAddProduct']);
        Route::post('/submit',['uses'=>'TransactionController@postSubmit']);
        Route::post('/apply_coupon',['uses'=>'TransactionController@postApplyCoupon']);
        Route::post('/{transaction_id}/resubmit_data',['uses'=>'TransactionController@postResubmitData']);
        Route::post('/{transaction_id}/send_payment_proof',['uses'=>'TransactionController@postSendPaymentProof']);
        Route::post('/{transaction_id}/reject',['uses'=>'TransactionController@postReject']);
        Route::post('/{transaction_id}/prepare_shipment',['uses'=>'TransactionController@postPrepareShipment']);
        Route::post('/{transaction_id}/shipped',['uses'=>'TransactionController@postShipped']);
        Route::post('/{transaction_id}/received',['uses'=>'TransactionController@postReceived']);
        Route::get('/{transaction_id}/price',['uses'=>'TransactionController@getPrice']);
        Route::get('/{transaction_id}/status',['uses'=>'TransactionController@getStatus']);
        Route::get('/track_shipment',['uses'=>'TransactionController@getTrackShipment']);
        Route::get('/{transaction_id}',['uses'=>'TransactionController@getRead']);
        Route::get('/cart_product_quantity/{product_id}',['uses'=>'TransactionController@getCartProductQuantity']);
    });
});
