<?php

namespace App\Domains\Repos;


use App\Models\Coupon;

/**
 * Interface CouponRepo
 * @package App\Domains\Repos
 */
interface CouponRepo
{
    /**
     * @param $code
     * @return Coupon
     */
    public function findByCode($code);

    /**
     * @param Coupon $coupon
     */
    public function save(Coupon $coupon);
}