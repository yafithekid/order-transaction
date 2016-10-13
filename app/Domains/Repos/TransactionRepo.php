<?php

namespace App\Domains\Repos;


use App\Models\Customer;
use App\Models\Transaction;

interface  TransactionRepo
{
    /**
     * @param Transaction $transaction
     */
    function save(Transaction $transaction);

    /**
     * @param Customer $customer
     * @param $submitted
     * @return Transaction
     */
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