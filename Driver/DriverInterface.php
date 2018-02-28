<?php
namespace App\Extend\Cart\Driver;

interface DriverInterface
{
    public function save(array $data, $cart_id);

    public function save_item(array $data, $item_id);

    public function del($cart_id);

    public function del_item($item_id);
}