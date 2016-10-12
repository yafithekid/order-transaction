<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\TransactionRepo;
use App\Models\Transaction;

class EloquentTransactionRepo implements TransactionRepo
{

    function save(Transaction $transaction)
    {
        return $transaction->save();
    }
}