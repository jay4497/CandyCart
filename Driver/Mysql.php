<?php
namespace App\Extend\Cart\Driver;

class Mysql implements DriverInterface
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function save(array $data, $cart_id = null)
    {
        if(empty($cart_id)){
            if(!$this->CI->db->insert('cart', $data)){
                log_message('error', lang('candy_cart_database_error'));
                return false;
            }
            return true;
        }else{
            $this->CI->db->where('cart_id', $cart_id);
            if(!$this->CI->db->update('cart', $data)){
                log_message('error', lang('candy_cart_database_error'));
                return false;
            }
            return true;
        }
    }

    public function save_item(array $data, $item_id = null)
    {
        $cart_id = $data['cart_id'];
        $this->CI->db->select('SUM(total_price) as total_price, SUM(quantity) as quantity');
        $this->CI->db->where('cart_id', $cart_id);
        $_result = $this->CI->db->get('cart')->result();
        if(empty($_result)){
            log_message('error', lang('candy_cart_specified_cart_not_exists'));
            return false;
        }
        $cart = array_shift($_result);

        if(empty($item_id)){
            // 构建购物车数据
            $updated_price = $cart->total_price + $data['total_price'];
            $updated_quantity = $cart->quantity + $data['quantity'];
            $updated_cart = [
                'total_price' => $updated_price,
                'quantity' => $updated_quantity,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];

            $this->CI->db->trans_start();
            $this->CI->db->insert('cart_detail', $data);
            $this->CI->db->where('cart_id', $cart_id);
            $this->CI->db->update('cart', $updated_cart);
            $this->CI->db->trans_complete();
            if($this->CI->db->trans_status() === false){
                log_message('error', lang('candy_cart_database_error'));
                return false;
            }
            return true;
        }else{
            $this->CI->db->select('total_price, quantity');
            $this->CI->db->where('detail_id', $item_id);
            $_result = $this->CI->db->get('cart_detail')->result();
            if(empty($_result)){
                log_message('error', lang('candy_cart_target_not_exists'));
                return false;
            }
            $item = array_shift($_result);

            $ex_price = $data['total_price'] - $item->total_price;
            $ex_quantity = $data['quantity'] - $item->quantity;
            $updated_price = $cart->total_price + $ex_price;
            $updated_quantity = $cart->quantity + $ex_quantity;
            $updated_cart = [
                'total_price' => $updated_price,
                'total_quantity' => $updated_quantity,
                'updated_by' => date('Y-m-d H:i:s', time())
            ];

            $this->CI->db->trans_start();
            $this->CI->db->where('detail_id', $item_id);
            $this->CI->db->update('cart_detail', $data);
            $this->CI->db->where('cart_id', $cart_id);
            $this->CI->db->update('cart', $updated_cart);
            $this->CI->db->trans_complete();
            if($this->CI->db->trans_status() === false){
                log_message('error', lang('candy_cart_database_error'));
                return false;
            }
            return true;
        }
    }

    public function del($cart_id)
    {
        $this->CI->db->where('cart_id', $cart_id);
        $_result = $this->CI->db->get('cart')->result();
        if(empty($_result)){
            log_message('error', lang('candy_cart_target_not_exists'));
            return false;
        }
        $cart = array_shift($_result);
        if($cart->user_id > 0){
            $clear_data = [
                'total_price' => 0,
                'quantity' => 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            $this->CI->db->trans_start();
            $this->CI->db->where('cart_id', $cart_id);
            $this->CI->db->delete('cart_detail');
            $this->CI->db->where('cart_id', $cart_id);
            $this->CI->db->update('cart', $clear_data);
            $this->CI->db->trans_complete();
            if($this->CI->db->trans_status() === false){
                log_message('error', lang('candy_cart_database_error'));
                return false;
            }
            return true;
        }else{
            $this->CI->db->trans_start();
            $this->CI->db->where('cart_id', $cart_id);
            $this->CI->db->delete('cart_detail');
            $this->CI->db->where('cart_id', $cart_id);
            $this->CI->db->delete('cart');
            $this->CI->db->trans_complete();
            if($this->CI->db->trans_status() === false){
                log_message('error', lang('candy_cart_database_error'));
                return false;
            }
            return true;
        }
    }

    public function del_item($item_id)
    {
        $this->CI->db->select('total_price, quantity');
        $this->CI->db->where('detail_id', $item_id);
        $_result = $this->CI->db->get('cart_detail')->result();
        if(empty($_result)){
            log_message('error', lang('candy_cart_target_not_exists'));
            return false;
        }
        $item = array_shift($_result);

        $this->CI->db->select('total_price, quantity');
        $this->CI->db->where('cart_id', $item->cart_id);
        $_result = $this->CI->db->get('cart')->result();
        if(empty($_result)){
            log_message('error', lang('candy_cart_target_not_exists'));
            return false;
        }
        $cart = array_shift($_result);

        $updated_price = $cart->total_price - $item->total_price;
        $updated_quantity = $cart->quantity - $item->quantity;
        $updated_cart = [
            'total_price' => $updated_price,
            'quantity' => $updated_quantity,
            'updated_at' => date('Y-m-d H:i:s', time())
        ];

        $this->CI->db->trans_start();
        $this->CI->db->where('detail_id', $item_id);
        $this->CI->db->delete('cart_detail');
        $this->CI->db->where('cart_id', $item->cart_id);
        $this->CI->db->update('cart', $updated_cart);
        $this->CI->db->trans_complete();
        if($this->CI->db->trans_status() === false){
            log_message('error', lang('candy_cart_database_error'));
            return false;
        }
        return true;
    }
}