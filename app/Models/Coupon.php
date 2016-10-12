<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property \DateTime valid_from
 * @property \DateTime valid_to
 */
class Coupon extends Model
{
    public $timestamps = false;

    protected $dates = ['valid_from','valid_to'];
}
