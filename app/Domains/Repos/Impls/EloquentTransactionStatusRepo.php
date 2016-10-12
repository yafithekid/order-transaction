<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\TransactionStatusRepo;
use App\Models\Transaction;
use App\Models\TransactionStatus;

class EloquentTransactionStatusRepo implements TransactionStatusRepo
{

    public function findByTransactionMostRecent(Transaction $transaction)
    {
        return $transaction->transactionStatuses()->orderBy('id','desc')->first();
    }

    public function save(TransactionStatus $transactionStatus){
        return $transactionStatus->save();
    }

    function findOrCreateByTransactionMostRecent(Transaction $transaction)
    {
        $transactionStatus = $this->findByTransactionMostRecent($transaction);
        if ($transactionStatus ==null){
            $transactionStatus = new TransactionStatus();
            $transactionStatus->status = TransactionStatus::STATUS_UNSUBMITTED;
            $transactionStatus->transaction()->associate($transaction);
            $this->save($transactionStatus);
        }
        return $transactionStatus;
    }
}