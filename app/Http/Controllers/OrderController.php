<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Interfaces\IOrder;
use App\Interfaces\IProduct;

class OrderController extends Controller
{
    protected $orderRepo;
    protected $prodRepo;
    public function __construct(IOrder $orderRepo, IProduct $prodRepo)
    {
        $this->middleware('auth:api');
        $this->orderRepo = $orderRepo;
        $this->prodRepo = $prodRepo;
    }

    public function getAllOrders()
    {
        $orders = $this->orderRepo->listAll();
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

                $order = $this->orderRepo->insertOrder(
                    $request->orderCode,
                    $user,
                    $productId,
                    $quantity,
                    $request->address,
                    $request->shippingDate
                );

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

        $user = ($credits->sub);

        $order = $this->orderRepo->findOrder($user, $orderCode)->get();

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

        $request->validate([
            'productId' => 'required|integer',
            'quantity' => 'required|integer',
            'address' => 'required|string',
            'shippingDate' => 'required|date'
        ]);

        $where =  $this->orderRepo->findOrder($user, $orderCode);

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
                $myProduct = $this->prodRepo->findProduct($request->productId);
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
                        $update = $this->orderRepo->updateOrder(
                            $user,
                            $orderCode,
                            $request->productId,
                            $request->quantity,
                            $request->address,
                            $request->shippingDate
                        );
                        if ($update) {
                            $order = $this->orderRepo->findOrder($user, $orderCode)->get();

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

    public function decreaseAmount()
    {
        $today =  date_create();
        $today = date_format($today, 'Y-m-d');
        $whereOrder = Order::where('shippingDate', '<=', $today)->where('isProcessed', '=', 0);
        $getOrder = $whereOrder->get();

        foreach ($getOrder as $myOrder) {
            $buyedAmount = $myOrder->quantity;
            $buyedProductId = (int)($myOrder->productId);
        }
        $getProduct = Product::find($buyedProductId);


        $num = $getProduct->amount - $buyedAmount;

        $getProduct->update([
            'amount' => $num
        ]);

        $whereOrder->update([
            'isProcessed' => 1
        ]);
        $allProducts = Product::all();
        return response()->json([
            "status" => "success",
            "message" => "product amounts decreased",
            "products" => $allProducts
        ]);
    }
}
