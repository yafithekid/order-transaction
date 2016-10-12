<?php

namespace App\Domains\Repos;


use App\Models\Customer;

interface CustomerRepo
{
    /**
     * @param $token
     * @return Customer
     */
    function findByToken($token);

    /**
     * @param $int
     * @return Customer
     */
    public function findById($int);
}