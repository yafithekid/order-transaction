<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\CustomerRepo;
use App\Models\Customer;

class EloquentCustomerRepo implements CustomerRepo
{

    function findByToken($token)
    {
        return Customer::where('token','=',$token)->first();
    }

    public function findById($int)
    {
        return Customer::where('id','=',$int)->first();
    }
}