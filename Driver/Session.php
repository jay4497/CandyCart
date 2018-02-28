<?php
namespace App\Extend\Cart\Driver;

class Session implements DriverInterface
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function save(array $data, $cart_id)
    {

    }

    public function save_item(array $data, $item_id)
    {

    }

    public function del($cart_id)
    {

    }

    public function del_item($item_id)
    {

    }
}