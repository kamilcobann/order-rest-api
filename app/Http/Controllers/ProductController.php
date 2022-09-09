<?php

namespace App\Http\Controllers;

use App\Interfaces\IProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    protected $prodRepo;

    public function __construct(IProduct $prodRepo)
    {
        $this->middleware('auth:api');
        $this->prodRepo = $prodRepo;
    }

    public function getAllProducts()
    {
        $products = $this->prodRepo->listAll();
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

        $product = $this->prodRepo->insertProduct($request->name, $request->amount);

        return response()->json([
            'status' => 'success',
            'message' => 'product created',
            'product' => $product
        ]);
    }

    public function getProductByID($id)
    {
        $product = $this->prodRepo->findProduct($id);
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
        $this->prodRepo->updateProd($request->name, $request->amount, $id);

        return response()->json([
            'status' => 'success',
            'message' => 'product updated',
        ]);
    }

    public function deleteProduct($id)
    {
        $product = $this->prodRepo->findProduct($id);
        $product->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'product deleted',
            'product' => $product
        ]);
    }
}
