<?php

namespace App\Domains\Repos;


use App\Models\Customer;
use App\Models\Transaction;

interface  TransactionRepo
{
    function save(Transaction $transaction);

    function findByCustomerAndSubmittedMostRecent(Customer $customer, $submitted);

    /**
     * @param Customer $customer
     * @return Transaction
     */
    function findCustomerTransactionCart(Customer $customer);

    /**
     * @param $transaction_id
     * @return Transaction
     */
    public function findById($transaction_id);

    /**
     * @param $input
     * @return Transaction
     */
    public function findByShippingId($input);
}