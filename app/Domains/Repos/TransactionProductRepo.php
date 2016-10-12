<?php

namespace App\Domains\Repos;


use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;

interface TransactionProductRepo
{
    /**
     * @param Transaction $transaction
     * @param Product $product
     * @return TransactionProduct
     */
    function findByTransactionAndProduct(Transaction $transaction,Product $product);

    /**
     * @param Transaction $transaction
     * @return TransactionProduct[]
     */
    function findAllByTransactionWithProduct(Transaction $transaction);

    function save(TransactionProduct $transactionProduct);

    function delete(TransactionProduct $transactionProduct);
}