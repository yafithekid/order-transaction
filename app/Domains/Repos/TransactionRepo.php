<?php

namespace App\Domains\Repos;


use App\Models\Transaction;

interface  TransactionRepo
{
    function save(Transaction $transaction);
}