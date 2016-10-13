<?php

namespace App\Domains\Repos;


use App\Models\Customer;

interface CustomerRepo
{
    /**
     * @param string $token
     * @return Customer
     */
    function findByToken($token);

    /**
     * @param integer $int
     * @return Customer
     */
    public function findById($int);

    /**
     * @return Customer[]
     */
    public function findAll();
}