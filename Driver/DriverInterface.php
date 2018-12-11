<?php
namespace App\Extend\Cart\Driver;

use App\Extend\Cart\CartItem;

interface DriverInterface
{
    /**
     * 购物车中添加/更新一个商品
     *
     * @param CartItem $item
     * @return bool
     */
    public function save_item(CartItem $item);

    /**
     * 清空购物车
     *
     * @param int $cart_id
     * @return bool
     */
    public function clear($cart_id);

    /**
     * 购物车中移除一个商品
     *
     * @param mixed $item
     * @return bool
     */
    public function del_item($item);
}
