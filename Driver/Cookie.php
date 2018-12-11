<?php
namespace App\Extend\Cart\Driver;

use App\Extend\Cart\CartItem;

class Cookie implements DriverInterface
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function save_item(CartItem $item)
    {

    }

    public function clear($cart_id)
    {

    }

    public function del_item($item_id)
    {

    }
}
