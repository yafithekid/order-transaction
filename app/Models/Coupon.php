<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property \DateTime valid_from
 * @property \DateTime valid_to
 * @property string code
 * @property int quantity
 * @property float percentage_cut
 * @property int paid_cut
 * @property int id
 */
class Coupon extends Model
{
    public $timestamps = false;

    protected $dates = ['valid_from','valid_to'];
}
