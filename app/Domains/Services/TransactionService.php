<?php

namespace App\Domains\Services;


use App\Domains\Exceptions\InvalidCouponException;
use App\Domains\Exceptions\NotEnoughCouponException;
use App\Domains\Exceptions\NotEnoughProductQuantityException;
use App\Domains\Exceptions\TransactionAlreadySubmittedException;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionStatus;

interface TransactionService
{
    /**
     * @param Customer $customer
     * @return Transaction
     */
    function findOrCreateTranscationCart(Customer $customer);

    /**
     * Add product to transaction, or remove the product from transaction if quantity is <= 0
     * @param Transaction $transaction
     * @param Product $product
     * @param $quantity
     * @return void
     * @throws TransactionAlreadySubmittedException when transaction had been submitted
     * @throws NotEnoughProductQuantityException when the product quantity is not enough
     */
    function addProduct(Transaction $transaction, Product $product, $quantity);

    /**
     * @param Transaction $transaction
     * @param Coupon $coupon
     * @return Transaction
     * @throws InvalidCouponException
     * @throws NotEnoughCouponException
     */
    function applyCoupon(Transaction $transaction,Coupon $coupon);

    /**
     * Submit the transaction and change the transaction status
     * @param Transaction $transaction
     * @return TransactionStatus
     */
    function submit(Transaction $transaction);

    /**
     * Give the payment proof and set the transaction status to 'need checking'
     * @param Transaction $transaction
     * @param string $payment_proof_url
     * @return TransactionStatus the most recent transaction status
     */
    function sendPaymentProof(Transaction $transaction, $payment_proof_url);

    /**
     * Reject the transaction and give its description why it is rejected.
     * @param Transaction $transaction
     * @param $description
     * @return TransactionStatus
     */
    function reject(Transaction $transaction,$description);

    /**
     * @param Transaction $transaction
     * @return mixed
     */
    function prepareShipment(Transaction $transaction);

    /**
     * @param Transaction $transaction
     * @param $shipping_id
     * @return TransactionStatus
     */
    function shipped(Transaction $transaction,$shipping_id);

    function received(Transaction $transaction);

    /**
     * Add new status to the transaction
     * @param Transaction $transaction
     * @param $status
     * @param null $description
     * @return TransactionStatus
     */
    function addStatus(Transaction $transaction,$status,$description = null);
}