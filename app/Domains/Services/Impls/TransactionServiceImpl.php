<?php

namespace App\Domains\Services\Impls;


use App\Domains\Exceptions\InvalidCouponException;
use App\Domains\Exceptions\NotEnoughProductQuantityException;
use App\Domains\Exceptions\TransactionAlreadySubmittedException;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Domains\Repos\TransactionStatusRepo;
use App\Domains\Services\TransactionService;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use App\Models\TransactionStatus;
use Carbon\Carbon;

class TransactionServiceImpl implements TransactionService
{
    private $transactionProductRepo;
    private $transactionStatusRepo;
    private $transactionRepo;
    private $productRepo;

    function __construct(ProductRepo $productRepo,TransactionRepo $transactionRepo,TransactionProductRepo $transactionProductRepo,TransactionStatusRepo $transactionStatusRepo)
    {
        $this->productRepo = $productRepo;
        $this->transactionRepo = $transactionRepo;
        $this->transactionProductRepo = $transactionProductRepo;
        $this->transactionStatusRepo = $transactionStatusRepo;
    }

    /**
     * @param Transaction $transaction
     * @param Product $product
     * @param $quantity
     * @throws NotEnoughProductQuantityException
     * @throws TransactionAlreadySubmittedException
     */
    function addProduct(Transaction $transaction, Product $product, $quantity)
    {
        //check first whether the transaction already submitted
        $transactionStatus = $this->transactionStatusRepo->findOrCreateByTransactionMostRecent($transaction);
        if($transactionStatus->isAlreadySubmitted())
            throw new TransactionAlreadySubmittedException();

        //check if product quantity is not enough
        $updated_count = $this->productRepo->decreaseQuantityWhereQuantityGreaterEq($product,$quantity,$quantity);
        if ($updated_count == 0)
            throw new NotEnoughProductQuantityException();

        $transactionProduct = $this->transactionProductRepo->findByTransactionAndProduct($transaction,$product);
        if ($transactionProduct != null){
            if ($quantity > 0){
                //update and save
                $transactionProduct->quantity = $quantity;
                $this->transactionProductRepo->save($transactionProduct);
            } else {
                //delete
                $this->transactionProductRepo->delete($transactionProduct);
            }
        } else {
            if ($quantity > 0){
                //create new
                $transactionProduct = new TransactionProduct();
                $transactionProduct->transaction()->associate($transaction);
                $transactionProduct->product()->associate($product);
                $transactionProduct->quantity = $quantity;
                $this->transactionProductRepo->save($transactionProduct);
            }
        }
    }

    /**
     * @param Transaction $transaction
     * @return TransactionStatus
     */
    function submit(Transaction $transaction)
    {
        $transaction->submitted = true;
        $this->transactionRepo->save($transaction);
        return $this->addStatus($transaction,TransactionStatus::STATUS_NEED_PAYMENT_PROOF);
    }

    /**
     * Add new status to the transaction
     * @param Transaction $transaction
     * @param $status
     * @param null $description
     * @return TransactionStatus
     */
    function addStatus(Transaction $transaction, $status, $description = null)
    {
        $transactionStatus = new TransactionStatus();
        $transactionStatus->transaction()->associate($transaction);
        $transactionStatus->status = $status;
        $transactionStatus->description = $description;
        $this->transactionStatusRepo->save($transactionStatus);
        return $transactionStatus;
    }

    /**
     * AdConfirm the payment of transaction is ok and change the transaction status
     * @param Transaction $transaction
     * @param string $payment_proof_url
     * @return TransactionStatus
     */
    function sendPaymentProof(Transaction $transaction,$payment_proof_url)
    {
        $transaction->payment_url = $payment_proof_url;
        $this->transactionRepo->save($transaction);
        //if the last status is not "need checking", change it
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        if (!$transactionStatus->isNeedChecking()){
            $transactionStatus = $this->addStatus($transaction,TransactionStatus::STATUS_NEED_CHECKING,null);
        }
        return $transactionStatus;
    }

    /**
     * Reject the transaction and give its description why it is rejected.
     * @param Transaction $transaction
     * @param $description
     * @return TransactionStatus
     */
    function reject(Transaction $transaction, $description)
    {
        return $this->addStatus($transaction,TransactionStatus::STATUS_REJECTED,$description);
    }

    /**
     * @param Transaction $transaction
     * @return mixed
     */
    function prepareShipment(Transaction $transaction)
    {
        return $this->addStatus($transaction,TransactionStatus::STATUS_PREPARED_FOR_SHIPMENT);
    }

    /**
     * @param Transaction $transaction
     * @param $shipping_id
     * @return TransactionStatus
     */
    function shipped(Transaction $transaction, $shipping_id)
    {
        $transaction->shipping_id = $shipping_id;
        $this->transactionRepo->save($transaction);
        return $this->addStatus($transaction,TransactionStatus::STATUS_SHIPPED);
    }

    function received(Transaction $transaction)
    {
        return $this->addStatus($transaction,TransactionStatus::STATUS_RECEIVED);
    }

    /**
     * @param Transaction $transaction
     * @param Coupon $coupon
     * @return Transaction
     * @throws InvalidCouponException
     */
    function applyCoupon(Transaction $transaction, Coupon $coupon)
    {
        $now = Carbon::now();
        if ($now < $coupon->valid_from || $now > $coupon->valid_to){
            throw new InvalidCouponException();
        }
        $transaction->coupon()->associate($coupon);
        $this->transactionRepo->save($transaction);
        return $transaction;
    }

    function findOrCreateTranscationCart(Customer $customer)
    {
        $transaction = $this->transactionRepo->findByCustomerAndSubmittedMostRecent($customer,false);
        if ($transaction == null){
            $transaction = new Transaction();
            $transaction->customer()->associate($customer);
            $this->transactionRepo->save($transaction);
        }
        return $transaction;
    }
}