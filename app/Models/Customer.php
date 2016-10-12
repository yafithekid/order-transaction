<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string name
 * @property string email
 * @property string address
 * @property string token
 */
class Customer extends Model
{
    public $timestamps = false;
}
