<?php
/**
 * 购物车类
 */

namespace App\Extend\Cart;

class CandyCart
{
    protected $CI;

    protected $user;

    protected $cart;

    public function __construct($params = [])
    {
        $this->CI =& get_instance();

        if(array_key_exists('user_id', $params)){
            $user_id = $params['user_id'];
            $this->get_user($user_id);
            return $this;
        }
        if(isset($_SESSION['user_id'])){
            $user_id = $_SESSION['user_id'];
            $this->get_user($user_id);
            return $this;
        }
        if(!empty($this->input->get_post('user_id'))){
            $user_id = $this->input->get_post('user_id');
            $this->get_user($user_id);
            return $this;
        }

        log_message('info', lang('Class CandyCart initialized.'));
    }

    private function init()
    {
        if (!empty($this->user)) {
            $user_id = $this->user->user_id;
            $_result = $this->CI->db->where('user_id', $user_id)->get('cart')->result();
            if (empty($_result)) {
                $_data = [
                    'user_id' => $user_id,
                    'created_at' => date('Y-m-d H:i:s', time()),
                    'updated_at' => date('Y-m-d H:i:s', time())
                ];
                if ($this->CI->db->insert('cart', $_data)) {
                    $cart_id = $this->CI->db->insert_id();
                    $_result = $this->CI->db->where('cart_id', $cart_id)->get('cart')->result();
                    $this->cart = array_shift($_result);
                    return true;
                } else {
                    log_message('error', lang('candy_cart_database_error'));
                    return false;
                }
            } else {
                $this->cart = array_shift($_result);
                return true;
            }
        } else {
            $_data = [
                'user_id' => 0,
                'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            if ($this->CI->db->insert('cart', $_data)) {
                $cart_id = $this->CI->db->insert_id();
                $_result = $this->CI->db->where('cart_id', $cart_id)->get('cart')->result();
                $this->cart = array_shift($_result);
                return true;
            } else {
                log_message('error', lang('candy_cart_database_error'));
                return false;
            }
        }
    }

    public function add($item = [])
    {
        if(empty($this->cart)){
            log_message('error', lang('candy_cart_cart_not_initialized'));
            return false;
        }

        // the data of cart to update
        $update_total_price = $this->cart->total_price + $item['total_price'];
        $update_quantity = $this->cart->quantity + $item['quantity'];
        $updated_at = date('Y-m-d H:i:s', time());
        $_update_cart_data = [
            'quantity' => $update_quantity,
            'total_price' => $update_total_price,
            'updated_at' => $updated_at
        ];

        $item['cart_id'] = $this->cart->cart_id;
        $this->CI->db->trans_start();
        $this->CI->db->insert('cart_detail', $item);
        $this->CI->db->where('cart_id', $this->cart->cart_id)->update('cart', $_update_cart_data);
        $this->CI->db->trans_complete();
        if($this->CI->db->trans_status()){
            return true;
        }else{
            log_message('error', lang('candy_cart_database_error'));
            return false;
        }
    }

    private function get_user($user_id)
    {
        $_result = $this->CI->db->where('user_id', $user_id)->get('user')->result();
        if(empty($_result)){
            log_message('error', lang('candy_cart_user_not_exists'));
        }
        $this->user = array_shift($_result);
    }
}