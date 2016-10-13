<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\CouponRepo;
use App\Models\Coupon;

class EloquentCouponRepo implements CouponRepo
{

    public function findByCode($code)
    {
        return Coupon::where('code','=',$code)->first();
    }

    public function save(Coupon $coupon)
    {
        return $coupon->save();
    }
}