<?php

use App\Domains\Repos\CustomerRepo;
use App\Domains\Repos\Impls\EloquentCustomerRepo;
use App\Domains\Repos\Impls\EloquentProductRepo;
use App\Domains\Repos\Impls\EloquentTransactionProductRepo;
use App\Domains\Repos\Impls\EloquentTransactionRepo;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Http\Controllers\Api\V1\ResponseCode;
use App\Http\Controllers\Api\V1\ResponseStatus;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

class TransactionTest extends TestCase
{
    /**
     * @var TransactionRepo
     */
    private $transactionRepo;
    /**
     * @var TransactionProductRepo
     */
    private $transactionProductRepo;
    /**
     * @var ProductRepo
     */
    private $productRepo;
    /**
     * @var CustomerRepo
     */
    private $customerRepo;

    /**
     * @var Customer[]
     */
    private $customers;
    /**
     * @var Product[]
     */
    private $products;

    /**
     *
     */
    private function instantiates()
    {
        if ($this->productRepo == null) {
            $this->productRepo = app()->make(ProductRepo::class);
        }
        if ($this->transactionProductRepo == null) {
            $this->transactionProductRepo = app()->make(TransactionProductRepo::class);
        }
        if ($this->customerRepo == null) {
            $this->customerRepo = app()->make(CustomerRepo::class);
        }
        if ($this->transactionRepo == null){
            $this->transactionRepo = app()->make(TransactionRepo::class);
        }
        if ($this->customers == null) {
            $_customers = $this->customerRepo->findAll();
            $this->customers = new Collection();
            foreach ($_customers as $customer) {
                $this->customers->put($customer->id, $customer);
            }
        }
        if ($this->products == null) {
            $_products = $this->productRepo->findAll();
            $this->products = new Collection();
            foreach ($_products as $product) {
                $this->products->put($product->id, $product);
            }
        }
    }

    public function testCustomerNotFound()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 2,
            'quantity' => 1,
            'token' => 'invalid token'
        ])->seeJson(['status'=>'error']);
    }

    public function testAddProduct()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 2,
            'quantity' => 1,
            'token' => 'token1'
        ])->seeJson(['status'=>'ok']);
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $transactionProduct = $this->transactionProductRepo->findByTransactionAndProduct($transaction,$this->products[2]);
        $this->assertEquals(1,$transactionProduct->quantity);
    }

    public function testModifyProduct()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 1,
            'quantity' => 10,
            'token' => 'token1'
        ])->seeJson(['status'=>'ok']);
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $transactionProduct = $this->transactionProductRepo->findByTransactionAndProduct($transaction,$this->products[1]);
        $this->assertEquals(10,$transactionProduct->quantity);
    }

    public function testNotEnoughProduct()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 1,
            'quantity' => 1000,
            'token' => 'token1'
        ])->seeJson(['status'=>'error','code'=>ResponseCode::PRODUCT_NOT_ENOUGH]);
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 1,
            'quantity' => 1,
            'token' => 'token1'
        ])->seeJson(['status'=>'ok']);
    }

}
