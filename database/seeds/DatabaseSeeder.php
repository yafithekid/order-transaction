<?php

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transaction::query()->delete();
        Admin::query()->delete();
        Coupon::query()->delete();
        Product::query()->delete();
        Customer::query()->delete();
        $this->call(AdminSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(CouponSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(TransactionSeeder::class);
    }
}
