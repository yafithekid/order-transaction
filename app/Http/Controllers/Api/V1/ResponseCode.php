<?php

namespace App\Http\Controllers\Api\V1;


class ResponseCode
{
    const CUSTOMER_NOT_FOUND = "cunf";

    const TRANSACTION_EMPTY_CART = "trec";
    const TRANSACTION_NOT_FOUND = "trnf";

    const PRODUCT_NOT_FOUND = "prnf";
    const PRODUCT_NOT_ENOUGH = "prnq";

    const COUPON_NOT_FOUND = "cpnf";
    const COUPON_INVALID = "cpiv";

}