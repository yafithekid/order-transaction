<?php

namespace App\Http\Controllers\Api\V1;


class JSONResponseFactory
{
    public static function ok(){
        return response()->json([
            'status' => ResponseStatus::OK,
            'message' => ''
        ]);
    }

    public static function customerNotFound(){
        return response()->json([
            'status' => ResponseStatus::ERROR,
            'message' => 'Customer not found',
            'code' => ResponseCode::CUSTOMER_NOT_FOUND
        ],404);
    }

    public static function productNotFound(){
        return response()->json([
            'status' => ResponseStatus::ERROR,
            'message' => 'Product not found',
            'code' => ResponseCode::PRODUCT_NOT_FOUND
        ],404);
    }

    public static function couponNotFound()
    {
        return response()->json([
            'status' => ResponseStatus::ERROR,
            'message' => 'Coupon not found',
            'code' => ResponseCode::COUPON_NOT_FOUND
        ],404);
    }

    public static function transactionNotFound()
    {
        return response()->json([
            'status' => ResponseStatus::ERROR,
            'message' => 'Transaction not found',
            'code' => ResponseCode::TRANSACTION_NOT_FOUND
        ],404);
    }

    public static function invalidAdminToken()
    {
        return response()->json([
            'status' => ResponseStatus::ERROR,
            'message' => 'Invalid admin token',
            'code' => ResponseCode::ADMIN_INVALID_TOKEN
        ],403);
    }

    public static function invalidCustomerToken()
    {
        return response()->json([
            'status' => ResponseStatus::ERROR,
            'message' => 'Invalid customer token',
            'code' => ResponseCode::CUSTOMER_INVALID_TOKEN
        ],403);
    }

}