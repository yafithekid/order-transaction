<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string payment_url
 * @property string shipping_id
 */
class Transaction extends Model
{
    public $timestamps = false;

    public function transactionStatuses(){
        return $this->hasMany(TransactionStatus::class,'transaction_id','id');
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class,'coupon_id','id');
    }
}
