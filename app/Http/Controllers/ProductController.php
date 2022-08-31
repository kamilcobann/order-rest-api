<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAllProducts()
    {
        $products = Product::all();
        return response()->json([
            'status' => 'success',
            'products' => $products
        ]);
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:6',
            'amount' => 'required|integer'
        ]);

        $product = Product::create(
            [
                'name' => $request->name,
                'amount' => $request->amount
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'product created',
            'product' => $product
        ]);
    }

    public function getProductByID($id)
    {
        $product = Product::find($id);
        return response()->json([
            'status' => 'success',
            'product' => $product
        ]);
    }

    public function updateProduct(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'amount' => 'integer'
        ]);
        $product = Product::find($id);

        $product->name = $request->name;
        $product->amount = $request->amount;

        $product->save();
        return response()->json([
            'status' => 'success',
            'message' => 'product updated',
            'product' => $product
        ]);
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);
        $product->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'product deleted',
            'product' => $product
        ]);
    }
}
