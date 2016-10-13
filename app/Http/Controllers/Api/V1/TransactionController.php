<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Exceptions\InvalidCouponException;
use App\Domains\Exceptions\NotEnoughCouponException;
use App\Domains\Exceptions\NotEnoughProductQuantityException;
use App\Domains\Repos\AdminRepo;
use App\Domains\Repos\CouponRepo;
use App\Domains\Repos\CustomerRepo;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Domains\Repos\TransactionStatusRepo;
use App\Domains\Services\TransactionService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    private $customerRepo;
    private $productRepo;
    private $transactionService;
    private $couponRepo;
    private $transactionRepo;
    private $transactionStatusRepo;
    private $transactionProductRepo;
    private $adminRepo;

    public function __construct(AdminRepo $adminRepo,TransactionProductRepo $transactionProductRepo,TransactionStatusRepo $transactionStatusRepo,TransactionRepo $transactionRepo,CouponRepo $couponRepo,ProductRepo $productRepo,CustomerRepo $customerRepo,TransactionService $transactionService)
    {
        $this->adminRepo = $adminRepo;
        $this->transactionProductRepo = $transactionProductRepo;
        $this->transactionStatusRepo = $transactionStatusRepo;
        $this->transactionRepo = $transactionRepo;
        $this->couponRepo = $couponRepo;
        $this->productRepo = $productRepo;
        $this->transactionService = $transactionService;
        $this->customerRepo = $customerRepo;
    }

    public function postAddProduct(Request $request){
        $customer = $this->customerRepo->findByToken($request->input('token'));
        $product = $this->productRepo->findById($request->input('product_id'));
        $qty = $request->input('quantity');
        if ($customer == null){
            return JSONResponseFactory::invalidCustomerToken();
        }
        if ($product == null){
            return JSONResponseFactory::productNotFound();
        }
        $transaction = $this->transactionService->findOrCreateTranscationCart($customer);
        try  {
            $this->transactionService->addProduct($transaction,$product,$qty);
            return JSONResponseFactory::ok();
        } catch (NotEnoughProductQuantityException $e){
            return response()->json([
                'status' => ResponseStatus::ERROR,
                'message' => 'Not enough product',
                'code' => ResponseCode::PRODUCT_NOT_ENOUGH
            ],422);
        }
    }

    public function postSubmit(Request $request){
        $customer = $this->customerRepo->findByToken($request->input('token'));
        if ($customer == null){
            return JSONResponseFactory::invalidCustomerToken();
        }
        $address = $request->input('address',$customer->address);
        $email = $request->input('email',$customer->email);
        $customer_name = $request->input('customer_name',$customer->name);
        $phone = $request->input('phone',$customer->phone);
        $transaction = $this->transactionService->findOrCreateTranscationCart($customer);
        $this->transactionService->submit($transaction,$customer_name,$phone,$email,$address);
        return JSONResponseFactory::ok();
    }

    public function postApplyCoupon(Request $request){
        $customer = $this->customerRepo->findByToken($request->input('token'));
        $coupon = $this->couponRepo->findByCode($request->input('code'));
        if ($customer == null){
            return JSONResponseFactory::invalidCustomerToken();
        }
        if ($coupon == null){
            return JSONResponseFactory::couponNotFound();
        }
        $transaction = $this->transactionService->findOrCreateTranscationCart($customer);
        try {
            $this->transactionService->applyCoupon($transaction,$coupon);
            return JSONResponseFactory::ok();
        } catch (InvalidCouponException $e){
            return response()->json([
                'status' => ResponseStatus::ERROR,
                'message' => 'Invalid coupon',
                'code' => ResponseCode::COUPON_INVALID
            ]);
        } catch (NotEnoughCouponException $e){
            return response()->json([
                'status' => ResponseStatus::ERROR,
                'message' => 'Not enough coupon quantity',
                'code' => ResponseCode::COUPON_NOT_ENOUGH
            ]);
        }
    }

    public function postSendPaymentProof($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction->customer->token != $request->input('token')){
            return JSONResponseFactory::invalidCustomerToken();
        }
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        $this->transactionService->sendPaymentProof($transaction,$request->input('payment_url'));
        return JSONResponseFactory::ok();
    }

    public function postResubmitData($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        if ($transaction->customer->token != $request->input('token')){
            return JSONResponseFactory::invalidCustomerToken();
        }
        $email = $request->input('email');
        $customer_name = $request->input('customer_name');
        $phone = $request->input('phone');
        $address = $request->input('address');
        $payment_url =  $request->input('payment_url');
        $this->transactionService->resubmitData($transaction,$payment_url,$customer_name,$phone,$email,$address);
        return JSONResponseFactory::ok();
    }

    public function postReject($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        if ($this->adminRepo->findByToken($request->input('token')) == null){
            return JSONResponseFactory::invalidAdminToken();
        }
        $this->transactionService->reject($transaction,$request->input('description'));
        return JSONResponseFactory::ok();
    }

    public function postPrepareShipment($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        if ($this->adminRepo->findByToken($request->input('token')) == null){
            return JSONResponseFactory::invalidAdminToken();
        }
        $this->transactionService->prepareShipment($transaction);
        return JSONResponseFactory::ok();
    }

    public function postShipped($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        if ($this->adminRepo->findByToken($request->input('token')) == null){
            return JSONResponseFactory::invalidAdminToken();
        }
        $this->transactionService->shipped($transaction,$request->input('shipping_id'));
        return JSONResponseFactory::ok();
    }

    public function postReceived($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction_id == null){
            return JSONResponseFactory::transactionNotFound();
        }
        if ($this->adminRepo->findByToken($request->input('token')) == null){
            return JSONResponseFactory::invalidAdminToken();
        }
        $this->transactionService->received($transaction);
        return JSONResponseFactory::ok();
    }

    public function getTrackShipment(Request $request){
        $transaction = $this->transactionRepo->findByShippingId($request->input('shipping_id'));
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        } else {
            $statuses = $this->transactionStatusRepo->findAllByTransactionOrderByMostRecent($transaction);
            return response()->json([
                'status' => ResponseStatus::OK,
                'message' => '',
                'data' => $statuses
            ]);
        }
    }

    public function getRead($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        } else {
            return response()->json([
                'status' => ResponseStatus::OK,
                'message' => '',
                'data' => $transaction
            ]);
        }
    }

    public function getPrice($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        $transactionProducts = $this->transactionProductRepo->findAllByTransactionWithProduct($transaction);
        $gross_price = 0;
        foreach ($transactionProducts as $transactionProduct){
            $gross_price += $transactionProduct->quantity * $transactionProduct->product->price;
        }
        $coupon = $transaction->coupon;
        if ($coupon == null){
            $net_price = $gross_price;
        } elseif ($coupon->percentage_cut > 0){
            $net_price = (1.0 - $coupon->percentage_cut) * $gross_price;
        } elseif ($coupon->paid_cut > 0){
            $net_price = max(0,$gross_price - $coupon->paid_cut);
        } else {
            $net_price = 0;
        }
        return response()->json([
            'status' => ResponseStatus::OK,
            'message' => '',
            'gross_price' => $gross_price,
            'net_price' => $net_price
        ]);
    }

    public function getCartProductQuantity($product_id,Request $request){
        $customer = $this->customerRepo->findByToken($request->input('token'));
        $product = $this->productRepo->findById($product_id);
        if ($customer == null){
            return JSONResponseFactory::invalidCustomerToken();
        }
        if ($product == null){
            return JSONResponseFactory::productNotFound();
        }
        $transaction = $this->transactionService->findOrCreateTranscationCart($customer);
        $transactionProduct = $this->transactionProductRepo->findByTransactionAndProduct($transaction,$product);
        if ($transactionProduct == null){
            $quantity = 0;
        } else {
            $quantity = $transactionProduct->quantity;
        }
        return response()->json([
            'status' => ResponseStatus::OK,
            'message' => '',
            'quantity' => intval($quantity)
        ]);
    }



}
