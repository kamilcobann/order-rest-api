<?php

namespace App\Interfaces;

interface IOrder
{

    public function listAll();
    public function insertOrder(
        $orderCode,
        $userId,
        $productId,
        $quantity,
        $address,
        $shippingDate
    );
    public function findOrder($user, $orderCode);
    public function updateOrder($user, $orderCode, $productId, $quantity, $address, $shippingDate);
}
