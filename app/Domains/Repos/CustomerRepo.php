<?php

namespace App\Domains\Repos;


interface CustomerRepo
{
    function findByToken($token);
}