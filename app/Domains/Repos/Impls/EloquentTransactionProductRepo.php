<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\TransactionProductRepo;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;

class EloquentTransactionProductRepo implements TransactionProductRepo
{

    function findByTransactionAndProduct(Transaction $transaction, Product $product)
    {
        return TransactionProduct::where('transaction_id','=',$transaction->id)
            ->where('product_id','=',$product->id)
            ->first();
    }

    function save(TransactionProduct $transactionProduct)
    {
        return $transactionProduct->save();
    }

    function delete(TransactionProduct $transactionProduct)
    {
        return $transactionProduct->delete();
    }

    /**
     * @param Transaction $transaction
     * @return TransactionProduct[]
     */
    function findAllByTransactionWithProduct(Transaction $transaction)
    {
        return TransactionProduct::where('transaction_id','=',$transaction->id)
            ->with(['product'])->get();
    }
}