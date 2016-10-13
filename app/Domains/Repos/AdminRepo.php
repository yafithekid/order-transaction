<?php

namespace App\Domains\Repos;


interface AdminRepo
{
    function findByToken($token);
}