<?php

namespace App\Domains\Repos;


use App\Models\Coupon;

interface CouponRepo
{
    /**
     * @param $code
     * @return Coupon
     */
    public function findByCode($code);

    public function save(Coupon $coupon);
}