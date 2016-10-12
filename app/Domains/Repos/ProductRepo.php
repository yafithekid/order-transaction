<?php

namespace App\Domains\Repos;


use App\Models\Product;

interface ProductRepo
{
    public function increaseQuantity(Product $product,$increased_amount);

    public function decreaseQuantityWhereQuantityGreaterEq(Product $product,$decrease_amount,$gte_amount);
}