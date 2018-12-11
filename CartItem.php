<?php
namespace App\Extend\Cart;

class CartItem
{
    public $item_id;

    public $cart_id;

    public $name;

    public $specifications;

    public $thumb;

    public $price;

    public $quantity;

    public $total_price;

    public $created_at;

    public $updated_at;

    public function get_array()
    {
        return [
            'item_id' => $this->item_id,
            'cart_id' => $this->cart_id,
            'name' => $this->name,
            'specifications' => $this->specifications,
            'thumb' => $this->thumb,
            'price' => $this->price,
            'quantity' => $this->price,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
