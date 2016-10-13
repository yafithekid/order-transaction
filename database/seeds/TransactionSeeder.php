<?php

use App\Domains\Repos\CustomerRepo;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Domains\Services\TransactionService;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    private $transactionRepo;
    private $transactionService;
    private $customerRepo;
    private $productRepo;

    public function __construct(CustomerRepo $customerRepo, ProductRepo $productRepo, TransactionRepo $transactionRepo, TransactionService $transactionService)
    {
        $this->customerRepo = $customerRepo;
        $this->productRepo = $productRepo;
        $this->transactionRepo = $transactionRepo;
        $this->transactionService = $transactionService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (config('database.default') == 'pgsql'){
            DB::statement("SELECT setval('transactions_id_seq',1,FALSE)");
        }
        $firstCustomer = $this->customerRepo->findById(1);
        //1. ongoing transaction cart
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->addStatus($transaction,TransactionStatus::STATUS_UNSUBMITTED);
        //2. submitted transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->submit($transaction);
        //3. paid transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->sendPaymentProof($transaction,"http://lorempixel.com/125/125");
        //4. rejected transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->reject($transaction,"Invalid address");
        //5. prepared shipment transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->prepareShipment($transaction);
        //6. shipped transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->shipped($transaction,"123");
        //7. received transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->received($transaction);
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $transaction->coupon_id = 1;
        $this->transactionRepo->save($transaction);
        $this->transactionService->submit($transaction);
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $transaction->coupon_id = 2;
        $this->transactionRepo->save($transaction);
        $this->transactionService->submit($transaction);
    }

    private function mockTransactionWithProducts(Customer $customer){
        $product = $this->productRepo->findById(1);
        $transaction = new Transaction();
        $transaction->customer()->associate($customer);
        $this->transactionRepo->save($transaction);
        $this->transactionService->addProduct($transaction,$product,1);
        return $transaction;

    }
}
