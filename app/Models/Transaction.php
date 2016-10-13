<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string payment_url
 * @property string shipping_id
 * @property boolean submitted
 * @property integer coupon_id
 * @property Coupon coupon
 * @property string phone
 * @property string customer_name
 * @property string email
 * @property string address
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

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }

    public function isAlreadySubmitted()
    {
        return $this->submitted;
    }

}
