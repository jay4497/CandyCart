<?php
/**
 * 购物车类
 */

namespace App\Extend\Cart;

use App\Extend\Cart\Driver\Cookie;
use App\Extend\Cart\Driver\DriverInterface;
use App\Extend\Cart\Driver\Mysql;

class CandyCart
{
    protected $CI;

    /**
     * 购物车存储驱动
     *
     * @var  DriverInterface
     */
    protected $driver;

    protected $user;

    protected $cart;

    public function __construct($params = [])
    {
        $this->CI =& get_instance();

        if(array_key_exists('driver', $params)){
            $driver_name = $params['driver']?: 'mysql';
            switch ($driver_name){
                case 'mysql':
                    $this->driver = new Mysql();
                    break;
                case 'cookie':
                    $this->driver = new Cookie();
                    break;
                default:
                    $this->driver = new Mysql();
                    break;
            }
        }
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
        if(!empty($this->CI->input->get_post('user_id'))){
            $user_id = $this->CI->input->get_post('user_id');
            $this->get_user($user_id);
            return $this;
        }

        if(!$this->init()){
            throw new \Exception('初始化失败');
        }

        log_message('info', lang('Class CandyCart initialized.'));
    }

    /**
     * 初始化一个购物车
     *
     * @return bool
     */
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

    /**
     * 添加/更新一个商品
     *
     * @param CartItem $item 商品
     * @return bool
     */
    public function add_item(CartItem $item)
    {
        $item->cart_id = $item->cart_id?: $this->cart->cart_id;
        return $this->driver->save_item($item);
    }

    /**
     * 删除一个商品
     *
     * @param mixed $item 商品。可以是 CartItem 类型或 id
     * @return bool
     */
    public function del_item($item)
    {
        $item_id = $item;
        if(is_object($item)){
            $item_id = $item->item_id;
        }
        return $this->driver->del_item($item_id);
    }

    /**
     * 清空购物车
     *
     * @return bool
     */
    public function clear()
    {
        return $this->driver->clear($this->cart->cart_id);
    }

    /**
     * 获取当前用户
     *
     * @param int $user_id
     */
    private function get_user($user_id)
    {
        $_result = $this->CI->db->where('user_id', $user_id)->get('user')->result();
        if(empty($_result)){
            log_message('error', lang('candy_cart_user_not_exists'));
        }
        $this->user = array_shift($_result);
    }
}
