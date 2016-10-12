<?php

namespace App\Domains\Repos;


use App\Models\Transaction;
use App\Models\TransactionStatus;

interface TransactionStatusRepo
{
    /**
     * @param Transaction $transaction
     * @return TransactionStatus
     */
    function findOrCreateByTransactionMostRecent(Transaction $transaction);

    /**
     * @param Transaction $transaction
     * @return TransactionStatus
     */
    function findByTransactionMostRecent(Transaction $transaction);

    function save(TransactionStatus $transactionStatus);
}