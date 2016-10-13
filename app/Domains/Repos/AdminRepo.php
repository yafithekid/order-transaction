<?php

namespace App\Domains\Repos;

use App\Models\Admin;

/**
 * Interface AdminRepo
 * @package App\Domains\Repos
 */
interface AdminRepo
{
    /**
     * @param string $token
     * @return Admin
     */
    function findByToken($token);
}