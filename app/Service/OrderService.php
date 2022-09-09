<?php

namespace App\Service;

use App\Interfaces\IOrder;
use App\Models\Order;

class OrderService implements IOrder
{
    public function listAll()
    {
        return Order::all();
    }

    public function insertOrder($orderCode, $user, $productId, $quantity, $address, $shippingDate)
    {

        return Order::create([
            'orderCode' => $orderCode,
            'userId' => (int)$user,
            'productId' => $productId,
            'quantity' => $quantity,
            'address' => $address,
            'shippingDate' => $shippingDate
        ]);
    }

    public function findOrder($user, $orderCode)
    {
        return Order::where('userId', '=', (int)$user)->where('orderCode', '=', $orderCode);
    }

    public function updateOrder(
        $user,
        $orderCode,
        $productId,
        $quantity,
        $address,
        $shippingDate
    ) {
        return $this->findOrder($user, $orderCode)->update([
            'productId' => $productId,
            'quantity' => $quantity,
            'address' => $address,
            'shippingDate' => $shippingDate,

        ]);
    }
}
