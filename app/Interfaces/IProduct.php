<?php

namespace App\Interfaces;

interface IProduct
{

    public function listAll();
    public function insertProduct($name, $amount);
    public function findProduct($id);
    public function updateProd($name, $amount, $id);
}
