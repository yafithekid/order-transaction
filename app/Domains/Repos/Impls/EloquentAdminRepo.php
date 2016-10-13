<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\AdminRepo;
use App\Models\Admin;

class EloquentAdminRepo implements AdminRepo
{

    function findByToken($token)
    {
        return Admin::where('token','=',$token)->first();
    }
}