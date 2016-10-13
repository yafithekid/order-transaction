<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string status
 * @property string description
 * @property bool submitted
 */
class TransactionStatus extends Model
{
    const STATUS_UNSUBMITTED = "unsubmitted";
    const STATUS_NEED_PAYMENT_PROOF = "need_payment_proof";
    const STATUS_NEED_CHECKING = "need_checking";
    const STATUS_REJECTED = "rejected";
    const STATUS_PREPARED_FOR_SHIPMENT = "prepared_for_shipment";
    const STATUS_SHIPPED = "shipped";
    const STATUS_RECEIVED = "received";

    public function transaction(){
        return $this->belongsTo(Transaction::class,'transaction_id','id');
    }

    public function isAlreadySubmitted(){
        return $this->status != self::STATUS_UNSUBMITTED;
    }

    public function isNeedChecking(){
        return $this->status == self::STATUS_NEED_CHECKING;
    }

}
