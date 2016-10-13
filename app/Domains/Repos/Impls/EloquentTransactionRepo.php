<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\TransactionRepo;
use App\Models\Customer;
use App\Models\Transaction;

class EloquentTransactionRepo implements TransactionRepo
{

    function save(Transaction $transaction)
    {
        return $transaction->save();
    }

    function findByCustomerAndSubmittedMostRecent(Customer $customer, $submitted)
    {
        return Transaction::where('customer_id','=',$customer->id)->where('submitted','=',$submitted)->orderBy('id','desc')->first();
    }

    public function findById($transaction_id)
    {
        return Transaction::where('id','=',$transaction_id)->first();
    }

    public function findByShippingId($input)
    {
        return Transaction::where('shipping_id','=',$input)->first();
    }

    function findCustomerTransactionCart(Customer $customer)
    {
        return $this->findByCustomerAndSubmittedMostRecent($customer,false);
    }
}