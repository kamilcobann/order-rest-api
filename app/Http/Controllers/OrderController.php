<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Brick\Math\BigInteger;
use DateTime;
use GuzzleHttp\Handler\Proxy;
use Symfony\Component\VarDumper\VarDumper;

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

        $user = ($credits->sub);


        $request->validate([
            'orderCode' => 'required|string|max:8',
            'userId' => $user,
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
                    'userId' => (int)$user,
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

        $user = (array)($credits->sub);

        $order = Order::where('userId', '=', (int)$user)->where('orderCode', '=', $orderCode)->get();

        return response()->json([
            'status' => 'success',
            'product' => $order
        ]);
    }

    public function updateOrder(Request $request, $orderCode)
    {
        $token = $request->bearerToken();

        $credits = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));

        $user = (array)$credits->sub;

        $request->validate([
            'productId' => 'required|integer',
            'quantity' => 'required|integer',
            'address' => 'required|string',
            'shippingDate' => 'required|date'
        ]);

        $where =  Order::where('userId', '=', (int)$user)->where('orderCode', '=', $orderCode);

        $getOrder = $where->get();

        foreach ($getOrder as $myOrder) {
            $initial_shippingDate = ($myOrder->shippingDate);
        }

        $initDate = date_create($initial_shippingDate);
        $today =  date_create();


        if ($today > $initDate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipping date expired'
            ], 400);
        } else {
            if (!$getOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found!'
                ], 404);
            } else {
                $myProduct = Product::find($request->productId);
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
                        $update = $where->update([
                            'productId' => $request->productId,
                            'quantity' => $request->quantity,
                            'address' => $request->address,
                            'shippingDate' => $request->shippingDate
                        ]);
                        if ($update) {
                            $order = Order::where('userId', '=', (int)$user)->where('orderCode', '=', $orderCode)->get();

                            return response()->json([
                                'status' => 'success',
                                'message' => 'order updated successfully',
                                'order' => $order,

                            ]);
                        } else {
                            return response()->json([
                                'status' => 'error',
                                'message' => $update
                            ]);
                        }
                    }
                }
            }
        }
    }
}
