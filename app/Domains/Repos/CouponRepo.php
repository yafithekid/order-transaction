<?php

namespace App\Domains\Repos;


interface CouponRepo
{
    public function findByCode($code);
}