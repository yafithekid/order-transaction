<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Exceptions\InvalidCouponException;
use App\Domains\Exceptions\NotEnoughProductQuantityException;
use App\Domains\Repos\CouponRepo;
use App\Domains\Repos\CustomerRepo;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Domains\Repos\TransactionStatusRepo;
use App\Domains\Services\TransactionService;
use App\Http\Controllers\Api\V1\JSONResponseFactory;
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

    public function __construct(TransactionStatusRepo $transactionStatusRepo,TransactionRepo $transactionRepo,CouponRepo $couponRepo,ProductRepo $productRepo,CustomerRepo $customerRepo,TransactionService $transactionService)
    {
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
            return JSONResponseFactory::customerNotFound();
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
            return JSONResponseFactory::customerNotFound();
        }
        $transaction = $this->transactionService->findOrCreateTranscationCart($customer);
        $this->transactionService->submit($transaction);
        return JSONResponseFactory::ok();
    }

    public function postApplyCoupon(Request $request){
        $customer = $this->customerRepo->findByToken($request->input('token'));
        $coupon = $this->couponRepo->findByCode($request->input('code'));
        $transaction = $this->transactionService->findOrCreateTranscationCart($customer);
        if ($customer == null){
            return JSONResponseFactory::customerNotFound();
        }
        if ($coupon == null){
            return JSONResponseFactory::couponNotFound();
        }
        try {
            $this->transactionService->applyCoupon($transaction,$coupon);
            return JSONResponseFactory::ok();
        } catch (InvalidCouponException $e){
            return response()->json([
                'status' => ResponseStatus::ERROR,
                'message' => 'Invalid coupon',
                'code' => ResponseCode::COUPON_INVALID
            ]);
        }
    }

    public function postSendPaymentProof($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        $this->transactionService->sendPaymentProof($transaction,$request->input('payment_url'));
        return JSONResponseFactory::ok();
    }

    public function postReject($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        $this->transactionService->reject($transaction,$request->input('description'));
        return JSONResponseFactory::ok();
    }

    public function postPrepareShipment($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        $this->transactionService->prepareShipment($transaction);
        return JSONResponseFactory::ok();
    }

    public function postShipped($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction == null){
            return JSONResponseFactory::transactionNotFound();
        }
        $this->transactionService->shipped($transaction,$request->input('shipping_id'));
        return JSONResponseFactory::ok();
    }

    public function postReceived($transaction_id,Request $request){
        $transaction = $this->transactionRepo->findById($transaction_id);
        if ($transaction_id == null){
            return JSONResponseFactory::transactionNotFound();
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
        if ($transaction_id == null){
            return JSONResponseFactory::transactionNotFound();
        } else {
            return response()->json([
                'status' => ResponseStatus::OK,
                'message' => '',
                'data' => $transaction
            ]);
        }
    }



}
