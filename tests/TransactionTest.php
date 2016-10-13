<?php

use App\Domains\Repos\CouponRepo;
use App\Domains\Repos\CustomerRepo;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Domains\Repos\TransactionStatusRepo;
use App\Http\Controllers\Api\V1\ResponseCode;
use App\Http\Controllers\Api\V1\ResponseStatus;
use App\Models\Coupon;
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
     * @var CouponRepo
     */
    private $couponRepo;

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
        if ($this->couponRepo == null){
            $this->couponRepo = app()->make(CouponRepo::class);
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
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Add product with invalid customer token.
     * It should create error
     */
    public function testAddProductCustomerNotFound()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 2,
            'quantity' => 1,
            'token' => 'invalid token'
        ])->seeJson(['status'=>ResponseStatus::ERROR]);
    }

    /**
     * Add product with valid customer token
     * It should modify the transaction product data in the database
     */
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

    /**
     * Modify product added to cart
     * The last result saved in database must equals the last quantity given.
     */
    public function testModifyProduct()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 1,
            'quantity' => 1,
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $this->json('post','/api/v1/transactions/add_product',[
            'product_id' => 1,
            'quantity' => 10,
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $transactionProduct = $this->transactionProductRepo->findByTransactionAndProduct($transaction,$this->products[1]);
        $this->assertEquals(10,$transactionProduct->quantity);
    }

    /**
     * Add with not enough product quantity
     * It should reject with error
     * But when the quantity is enough, it should not reject
     */
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

    /**
     * Apply coupon with invalid customer access token
     * It should reject with error.
     */
    public function testApplyCouponInvalidToken()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/apply_coupon',[
            'code' => 'k1'
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::CUSTOMER_INVALID_TOKEN]);
        $this->assertResponseStatus(403);
    }

    /**
     * Apply coupon
     * The transaction data in database should be updated.
     */
    public function testApplyCoupon()
    {
        $this->instantiates();

        //reset the customer coupon
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $transaction->coupon_id = null;
        $this->transactionRepo->save($transaction);

        //set coupon quantity to 10
        $coupon = $this->couponRepo->findByCode('k1');
        $coupon->quantity = 10;
        $this->couponRepo->save($coupon);

        //apply coupon
        $this->json('post','/api/v1/transactions/apply_coupon',[
            'code' => 'k1',
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);

        //ensure the coupon id data is updated
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $this->assertEquals(1,$transaction->coupon_id);

        //ensure the quantity of coupon is deducted
        $updatedCoupon = $this->couponRepo->findByCode('k1');
        $this->assertEquals(9,$updatedCoupon->quantity);
    }

    /**
     * Modify the coupon in transaction cart with another coupon
     * Old coupon quantity must be increased and new coupon quantity must be decreased.
     */
    public function testModifyCoupon()
    {
        $this->instantiates();

        //reset the customer coupon
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        $transaction->coupon_id = null;
        $this->transactionRepo->save($transaction);

        //set coupon quantity to 10
        Coupon::query()->whereIn('id',[1,2])->update(['quantity'=>10]);

        $this->json('post','/api/v1/transactions/apply_coupon',[
            'code' => 'k1',
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);

        $this->assertEquals(1,Coupon::where('code','=','k1')->where('quantity','=',9)->count());
        $this->json('post','/api/v1/transactions/apply_coupon',[
            'code' => 'k2',
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $this->assertEquals(1,Coupon::where('code','=','k1')->where('quantity','=',10)->count());
        $this->assertEquals(1,Coupon::where('code','=','k2')->where('quantity','=',9)->count());
    }

    /**
     * Apply coupon with invalid date range (either expired or not active yet)
     * Ensure the coupon in transaction is not updated.
     */
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

    /**
     * Check if the price of transaction is deducted when a coupon is applied.
     */
    public function testPercentageCutCoupon()
    {
        $this->json('get','/api/v1/transactions/8/price',[])
            ->seeJson(['status'=>ResponseStatus::OK,'gross_price'=>10000,'net_price'=>9000]);
    }

    /**
     * Check if the price of transaction is deducted when a coupon is applied.
     */
    public function testPaidCutCoupon()
    {
        $this->json('get','/api/v1/transactions/9/price',[])
            ->seeJson(['status'=>ResponseStatus::OK,'gross_price'=>10000,'net_price'=>0]);
    }

    /**
     * Submit order transaction with invalid token
     */
    public function testSubmitInvalidToken()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/submit',[
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::CUSTOMER_INVALID_TOKEN]);
    }

    /**
     * Submit order transaction with no additional data (customer name, email, etc.)
     * Should give the transaction data with default customer data.
     */
    public function testSubmitNoData()
    {
        $this->instantiates();
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[1]);
        //submit the transaction
        $this->json('post','api/v1/transactions/submit',[
            'token' => 'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $updatedTransaction = $this->transactionRepo->findById($transaction->id);
        $customer = $this->customers[1];
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_NEED_PAYMENT_PROOF,$transactionStatus->status);
        $this->assertEquals($customer->name,$updatedTransaction->customer_name);
        $this->assertEquals($customer->address,$updatedTransaction->address);
        $this->assertEquals($customer->phone,$updatedTransaction->phone);
        $this->assertEquals($customer->email,$updatedTransaction->email);
    }

    /**
     * Submit order transaction with additional data (customer name, email, etc.)
     * Should be saved into database.
     */
    public function testSubmitWithData(){
        $this->instantiates();
        $transaction = $this->transactionRepo->findCustomerTransactionCart($this->customers[2]);
        $this->json('post','/api/v1/transactions/submit',[
            'token' => 'token2',
            'address' => 'a',
            'customer_name' => 'a',
            'email' => 'a@a.com',
            'phone' => '01234'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $updatedTransaction = $this->transactionRepo->findById($transaction->id);
        $this->assertEquals('a',$updatedTransaction->customer_name);
        $this->assertEquals('a',$updatedTransaction->address);
        $this->assertEquals('a@a.com',$updatedTransaction->email);
        $this->assertEquals('01234',$updatedTransaction->phone);
    }

    /**
     * Upload the payment proof image and invoke send payment proof service with valid token.
     */
    public function testSendPaymentProof()
    {
        $this->instantiates();
        $url = 'http://lorempixel.com/200/200/';
        $this->json('post','api/v1/images/upload',[
            'image' => 'base64:1234567890'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $this->json('post','api/v1/transactions/2/send_payment_proof',[
            'payment_url' => $url,
            'token'=>'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);

        $transaction = $this->transactionRepo->findById(2);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals($transaction->payment_url,$url);
        $this->assertEquals(TransactionStatus::STATUS_NEED_CHECKING,$transactionStatus->status);
    }

    /**
     * Send payment proof with no valid token
     * Should reject the operation
     */
    public function testSendPaymentProofNoToken()
    {
        $this->instantiates();
        $this->json('post','api/v1/transactions/2/send_payment_proof',[
            'payment_url' => 'aaa',
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::CUSTOMER_INVALID_TOKEN]);
    }

    /**
     * Reject customer transaction
     * Should modify the data in database.
     */
    public function testReject()
    {
        $this->instantiates();
        $description = 'Invalid email address';
        $this->json('post','api/v1/transactions/3/reject',[
            'description' => $description,
            'token'=>'token1'
        ])->seeJson(['status' => ResponseStatus::OK]);

        $transaction = $this->transactionRepo->findById(3);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_REJECTED,$transactionStatus->status);
        $this->assertEquals($description,$transactionStatus->description);
    }

    /**
     * Resubmit data of a transaction
     * New data should be saved
     */
    public function testResubmitData()
    {
        $this->instantiates();
        $this->json('post','api/v1/transactions/3/resubmit_data',[
            'customer_name' => 'a',
            'email' => 'a@a.com',
            'address' => 'a',
            'phone' => '01234',
            'token'=>'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $transaction = $this->transactionRepo->findById(3);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_NEED_CHECKING,$transactionStatus->status);
    }

    /**
     * Invoke 'prepared for shipment' with invalid admin token
     */
    public function testPrepareForShipmentInvalidToken()
    {
        $this->json('post','api/v1/transactions/3/prepare_shipment',[
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::ADMIN_INVALID_TOKEN]);
        $this->assertResponseStatus(403);
    }

    /**
     * Invoke 'prepared for shipment' with valid admin token
     */
    public function testPrepareForShipment()
    {
        $this->instantiates();
        $this->json('post','api/v1/transactions/3/prepare_shipment',[
            'token'=>'token1'
        ])->seeJson(['status' => ResponseStatus::OK]);

        $transaction = $this->transactionRepo->findById(3);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_PREPARED_FOR_SHIPMENT,$transactionStatus->status);
    }

    /**
     * Invoke 'shipped' operation with invalid token
     */
    public function testShippedInvalidToken()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/5/shipped',[
            'shipping_id'=>'1234'
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::ADMIN_INVALID_TOKEN]);
    }

    /**
     * Invoke 'shipped' operation with valid token
     */
    public function testShipped()
    {
        $this->instantiates();
        $shipping_id = 'S1234';
        $this->json('post','/api/v1/transactions/5/shipped',[
            'shipping_id' => $shipping_id,
            'token'=>'token1'
        ])->seeJson(['status'=>ResponseStatus::OK]);
        $transaction = $this->transactionRepo->findById(5);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_SHIPPED,$transactionStatus->status);
        $this->assertEquals($shipping_id,$transaction->shipping_id);
        $this->json('get','/api/v1/transactions/track_shipment',[
            'shipping_id' => $shipping_id
        ])->seeJson(['status'=>ResponseStatus::OK]);
    }

    /**
     * Invoke 'received' operation with no valid admin token
     */
    public function testReceivedInvalidToken()
    {
        $this->instantiates();
        $this->json('post','/api/v1/transactions/5/shipped',[
        ])->seeJson(['status'=>ResponseStatus::ERROR,'code'=>ResponseCode::ADMIN_INVALID_TOKEN]);
    }

    /**
     * Set transaction as 'received'
     */
    public function testReceived()
    {
        $this->instantiates();
        $this->json('post','api/v1/transactions/6/received',[
            'token'=>'token1'
        ])->seeJson(['status' => ResponseStatus::OK]);
        $transaction = $this->transactionRepo->findById(6);
        $transactionStatus = $this->transactionStatusRepo->findByTransactionMostRecent($transaction);
        $this->assertEquals(TransactionStatus::STATUS_RECEIVED,$transactionStatus->status);
    }
}
