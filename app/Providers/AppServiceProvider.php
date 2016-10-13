<?php

namespace App\Providers;

use App\Domains\Repos\AdminRepo;
use App\Domains\Repos\CouponRepo;
use App\Domains\Repos\CustomerRepo;
use App\Domains\Repos\Impls\EloquentAdminRepo;
use App\Domains\Repos\Impls\EloquentCouponRepo;
use App\Domains\Repos\Impls\EloquentCustomerRepo;
use App\Domains\Repos\Impls\EloquentProductRepo;
use App\Domains\Repos\Impls\EloquentTransactionProductRepo;
use App\Domains\Repos\Impls\EloquentTransactionRepo;
use App\Domains\Repos\Impls\EloquentTransactionStatusRepo;
use App\Domains\Repos\ProductRepo;
use App\Domains\Repos\TransactionProductRepo;
use App\Domains\Repos\TransactionRepo;
use App\Domains\Repos\TransactionStatusRepo;
use App\Domains\Services\Impls\TransactionServiceImpl;
use App\Domains\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TransactionRepo::class,EloquentTransactionRepo::class);
        $this->app->bind(TransactionStatusRepo::class,EloquentTransactionStatusRepo::class);
        $this->app->bind(TransactionProductRepo::class,EloquentTransactionProductRepo::class);
        $this->app->bind(ProductRepo::class,EloquentProductRepo::class);
        $this->app->bind(CustomerRepo::class,EloquentCustomerRepo::class);
        $this->app->bind(CouponRepo::class,EloquentCouponRepo::class);
        $this->app->bind(AdminRepo::class,EloquentAdminRepo::class);

        $this->app->bind(TransactionService::class,TransactionServiceImpl::class);
    }
}
