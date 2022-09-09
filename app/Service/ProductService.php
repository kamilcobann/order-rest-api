<?php

namespace App\Service;

use App\Interfaces\IProduct;
use App\Models\Product;

class ProductService implements IProduct
{
    public function listAll()
    {
        return Product::all();
    }

    public function insertProduct($name, $amount)
    {
        return Product::create(
            [
                'name' => $name,
                'amount' => $amount
            ]
        );
    }

    public function updateProd($name, $amount, $id)
    {
        $product = $this->findProduct($id);

        $product->name = $name;
        $product->amount = $amount;
        return $product->save();
    }

    public function findProduct($id)
    {
        return Product::find($id);
    }
}
