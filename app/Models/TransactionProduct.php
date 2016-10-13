<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer quantity
 * @property Product product
 */
class TransactionProduct extends Model
{
    public $timestamps = false;

    public function transaction(){
        return $this->belongsTo(Transaction::class,'transaction_id','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
