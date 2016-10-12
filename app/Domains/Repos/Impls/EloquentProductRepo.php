<?php

namespace App\Domains\Repos\Impls;


use App\Domains\Repos\ProductRepo;
use App\Models\Product;

class EloquentProductRepo implements ProductRepo
{

    public function increaseQuantity(Product $product, $increased_amount)
    {
        return Product::where('id','=',$product->id)->increment('quantity',$increased_amount);
    }

    public function decreaseQuantityWhereQuantityGreaterEq(Product $product, $decrease_amount, $gte_amount)
    {
        return Product::where('id','=',$product->id)->where('quantity','>=',$gte_amount)->decrement('quanity',$decrease_amount);
    }
}