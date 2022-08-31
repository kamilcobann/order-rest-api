<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAllOrders()
    {

        $orders = Order::all();
        return response()->json([
            'status' => 'success',
            'orders' => $orders,
        ]);
    }


    public function createOrder(Request $request)
    {

        $token = $request->bearerToken();

        $credits = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));

        $user = (int)($credits->sub);


        $request->validate([
            'orderCode' => 'required|string|max:8',
            'productId' => 'required|integer',
            'quantity' => 'required|integer',
            'address' => 'required|string',
            'shippingDate' => 'required|date'
        ]);
        $productId = $request->productId;
        $quantity = $request->quantity;

        $myProduct = Product::find($productId);

        if (!$myProduct) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found!'
            ], 404);
        } else {
            if ($myProduct->amount < $request->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid quantity (more than amount or negative)!'
                ], 400);
            } else {
                $order = Order::create([
                    'orderCode' => $request->orderCode,
                    'userId' => $user,
                    'productId' => $productId,
                    'quantity' => $quantity,
                    'address' => $request->address,
                    'shippingDate' => $request->shippingDate
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'order created successfully',
                    'order' => $order
                ]);
            }
        }
    }

    public function getOrderByOrderCode(Request $request, $orderCode)
    {

        $token = $request->bearerToken();

        $credits = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));

        $user = $credits->sub;

        $order = Order::where(['userId', '=', $user], ['orderCode', '=', $orderCode])->get();

        return response()->json([
            'status' => 'success',
            'product' => $order
        ]);
    }

    public function updateOrder(Request $request, $orderCode)
    {
        $token = $request->bearerToken();

        $credits = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));

        $user = $credits->sub;

        $order = Order::where(['userId', '=', $user], ['orderCode', '=', $orderCode])->get();

        $request->validate([
            'productId' => 'required|integer',
            'quantity' => 'required|integer',
            'address' => 'required|string',
            'shippingDate' => 'required|date'
        ]);

        $productId = $request->productId;
        $quantity = $request->quantity;

        $myProduct = Product::find($productId);

        if (!$myProduct) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found!'
            ], 404);
        } else {
            if ($myProduct->amount < $request->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid quantity (more than amount or negative)!'
                ], 400);
            } else {
                $order = Order::create([
                    'productId' => $productId,
                    'quantity' => $quantity,
                    'address' => $request->address,
                    'shippingDate' => $request->shippingDate
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'order updated successfully',
                    'order' => $order
                ]);
            }
        }
    }
}
