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
        //ongoing transaction cart
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->addStatus($transaction,TransactionStatus::STATUS_UNSUBMITTED);
        //submitted transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->submit($transaction);
        //paid transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->sendPaymentProof($transaction,"http://lorempixel.com/125/125");
        //rejected transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->reject($transaction,"Invalid address");
        //prepared shipment transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->prepareShipment($transaction);
        //shipped transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->shipped($transaction,"123");
        //received transaction
        $transaction = $this->mockTransactionWithProducts($firstCustomer);
        $this->transactionService->received($transaction);
    }

    private function mockTransactionWithProducts(Customer $customer){
        $product = $this->productRepo->findById(1);
        $transaction = new Transaction();
        $transaction->customer()->associate($customer);
        $this->transactionService->addProduct($transaction,$product,1);
        return $transaction;

    }
}
