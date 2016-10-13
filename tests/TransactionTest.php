<?php

use App\Domains\Repos\CustomerRepo;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Domains\Repos\TransactionStatusRepo;
use App\Http\Controllers\Api\V1\ResponseCode;
use App\Http\Controllers\Api\V1\ResponseStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\TransactionStatus;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

class TransactionTest extends TestCase
{
    /**
     * @var TransactionStatusRepo
     */
    private $transactionStatusRepo;
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
        if ($this->transactionStatusRepo == null){
            $this->transactionStatusRepo = app()->make(TransactionStatusRepo::class);
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
        ])->seeJson(['status'=>ResponseStatus::ERROR]);
    }

    public function testAddProduct()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 2,
            'quantity' => 1,
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
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
        ])->seeJson(['status'=>ResponseStatus::OK]);
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
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::PRODUCT_NOT_ENOUGH]);
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 1,
            'quantity' => 1,
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
    }

    public function testApplyCoupon()
    {
        $this->instantiates();

        //reset the customer coupon
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $transaction->coupon_id = null;
        $this->transactionRepo->save($transaction);

        //apply coupon
        $this->json('post','/api/v1/transactions/apply_coupon',[
            'code' => 'k1',
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);

        //ensure the coupon id data is updated
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $this->assertEquals(1,$transaction->coupon_id);
    }

    public function testInvalidCouponDate()
    {
        $this->instantiates();
        //reset the customer coupon
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $transaction->coupon_id = null;
        $this->transactionRepo->save($transaction);

        //apply invalid coupon
        $this->json('post','api/v1/transactions/apply_coupon',[
            'token' => 'token1',
            'code' => 'k3'
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::COUPON_INVALID]);

        //ensure the coupon id is not updated
        $updatedTransaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $this->assertEquals($transaction->id,$updatedTransaction->id);
        $this->assertNull($updatedTransaction->coupon_id);

        $this->json('post','api/v1/transactions/apply_coupon',[
            'token' => 'token1',
            'code' => 'k4'
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::COUPON_INVALID]);
        //ensure the coupon id is not updated
        $updatedTransaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $this->assertEquals($transaction->id,$updatedTransaction->id);
        $this->assertNull($updatedTransaction->coupon_id);
    }

    public function testSubmit()
    {
        $this->instantiates();
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        //submit the transaction
        $this->json('post','api/v1/transactions/submit',[
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);

        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_NEED_PAYMENT_PROOF,$transactionStatus->status);
    }

    public function testSendPaymentProof()
    {
        $this->instantiates();
        $url = 'http://lorempixel.com/200/200/';
        $this->json('post','api/v1/transactions/2/send_payment_proof',[
            'payment_url' => $url
        ])->seeJson(['status'=>ResponseStatus::OK]);

        $transaction = $this->transactionRepo->findById(2);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals($transaction->payment_url,$url);
        $this->assertEquals(TransactionStatus::STATUS_NEED_CHECKING,$transactionStatus->status);
    }

    public function testReject()
    {
        $this->instantiates();
        $description = 'Invalid email address';
        $this->json('post','api/v1/transactions/3/reject',[
            'description' => $description
        ])->seeJson(['status' => ResponseStatus::OK]);

        $transaction = $this->transactionRepo->findById(3);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_REJECTED,$transactionStatus->status);
        $this->assertEquals($description,$transactionStatus->description);
    }

    public function testPrepareForShipment()
    {
        $this->instantiates();
        $this->json('post','api/v1/transactions/3/prepare_shipment',[
        ])->seeJson(['status' => ResponseStatus::OK]);

        $transaction = $this->transactionRepo->findById(3);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_PREPARED_FOR_SHIPMENT,$transactionStatus->status);
    }

    public function testShipped()
    {
        $this->instantiates();
        $this->json('post','api/v1/transactions/5/shipped',[

        ])->seeJson(['status'=>ResponseStatus::OK]);
        $transaction = $this->transactionRepo->findById(5);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_SHIPPED,$transactionStatus->status);
    }

    public function testReceived()
    {
        $this->instantiates();
        $this->json('post','api/v1/transactions/6/received',[
        ])->seeJson(['status' => ResponseStatus::OK]);
        $transaction = $this->transactionRepo->findById(6);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_RECEIVED,$transactionStatus->status);
    }
}
