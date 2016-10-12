<?php

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (config('database.default') == 'pgsql'){
            DB::statement("SELECT setval('customers_id_seq',1,FALSE)");
        }
        //create 3 customer
        for($i = 1; $i <= 3; $i++) {
            $customer = new Customer();
            $customer->name = "Pelanggan {$i}";
            $customer->email = "pelanggan{$i}@gmail.com";
            $customer->address = "Jalan Mawar {$i}";
            $customer->token = "token{$i}";
            $customer->save();
        }
    }
}
