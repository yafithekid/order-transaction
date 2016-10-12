<?php

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (config('database.default') == 'pgsql'){
            DB::statement("SELECT setval('coupons_id_seq',1,FALSE)");
        }
        for($i = 1; $i <= 2; $i++){
            $coupon = new Coupon();
            $coupon->code = "k{$i}";
            $coupon->quantity = 10;
            $coupon->valid_from = Carbon::now()->addYear(-1);
            $coupon->valid_to = Carbon::now()->addYear(1);
            if ($i == 1){
                $coupon->percentage_cut = 0.1;
            } else {
                $coupon->paid_cut = 10000;
            }
            $coupon->save();
        }
        //create invalid coupon
        for($i = 3; $i <= 4; $i++){
            $coupon = new Coupon();
            $coupon->code = "k{$i}";
            $coupon->quantity = 10;
            $coupon->paid_cut = 10000;
            if ($i == 3){
                $coupon->valid_from = Carbon::now()->addYear(-2);
                $coupon->valid_to = Carbon::now()->addYear(-1);
            } else {
                $coupon->valid_from = Carbon::now()->addYear(1);
                $coupon->valid_to = Carbon::now()->addYear(2);
            }
            $coupon->save();
        }
        //create empty coupon
        $coupon = new Coupon();
        $coupon->code = "k5";
        $coupon->quantity = 0;
        $coupon->paid_cut = 10000;
        $coupon->valid_from = Carbon::now()->addYear(-2);
        $coupon->valid_to = Carbon::now()->addYear(2);
        $coupon->save();
    }
}
