<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string name
 * @property integer price
 * @property int quantity
 */
class Product extends Model
{
    public $timestamps = false;
}
