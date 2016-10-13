<?php

namespace App\Domains\Repos;


use App\Models\Product;

interface ProductRepo
{
    /**
     * @param Product $product
     * @param integer $increased_amount
     * @return integer count of modified row
     */
    public function increaseQuantity(Product $product,$increased_amount);

    /**
     * @param Product $product
     * @param int $decrease_amount
     * @param int $gte_amount
     * @return integer count of modified row
     */
    public function decreaseQuantityWhereQuantityGreaterEq(Product $product,$decrease_amount,$gte_amount);

    /**
     * @param int $id
     * @return Product
     */
    public function findById($id);

    /**
     * @return Product[]
     */
    public function findAll();
}