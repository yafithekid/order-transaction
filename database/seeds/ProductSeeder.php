<?php

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (config('database.default') == 'pgsql'){
            DB::statement("SELECT setval('products_id_seq',1,FALSE)");
        }
        for($i = 1; $i <= 2; $i++){
            $product = new Product();
            $product->name = "Produk {$i}";
            $product->price = $i * 10000;
            $product->quantity = 100;
            $product->save();
        }
        //create product with 0 qty
        $product = new Product();
        $product->name = "Produk 3";
        $product->price = 10000;
        $product->quantity = 0;
        $product->save();
    }
}
