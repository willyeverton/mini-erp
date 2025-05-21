<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_orders() {
        $this->db->select('orders.*, users.name as user_name, users.email as user_email');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id');
        $this->db->order_by('orders.created_at', 'DESC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_user_orders($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');

        $query = $this->db->get('orders');
        return $query->result_array();
    }

    public function get_order($id) {
        $this->db->select('orders.*, users.name as user_name, users.email as user_email, coupons.code as coupon_code');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id');
        $this->db->join('coupons', 'coupons.id = orders.coupon_id', 'left');
        $this->db->where('orders.id', $id);

        $query = $this->db->get();
        return $query->row_array();
    }

    public function create_order($data) {
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }

    public function update_order($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('orders', $data);
    }

    public function add_order_item($data) {
        return $this->db->insert('order_items', $data);
    }

    public function get_order_items($order_id) {
        $this->db->select('order_items.*, products.name as product_name, products.image, product_variations.name as variation_name');
        $this->db->from('order_items');
        $this->db->join('products', 'products.id = order_items.product_id');
        $this->db->join('product_variations', 'product_variations.id = order_items.variation_id', 'left');
        $this->db->where('order_items.order_id', $order_id);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_sales_report($start_date = null, $end_date = null) {
        $this->db->select('DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue');
        $this->db->from('orders');
        $this->db->where('status !=', 'canceled');
        $this->db->where('DATE(created_at) >=', $start_date);
        $this->db->where('DATE(created_at) <=', $end_date);
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('DATE(created_at)', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_top_products($limit = 10, $start_date = null, $end_date = null) {
        $this->db->select('products.id, products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as total_revenue');
        $this->db->from('order_items');
        $this->db->join('products', 'products.id = order_items.product_id');
        $this->db->join('orders', 'orders.id = order_items.order_id');
        $this->db->where('orders.status !=', 'canceled');

        if ($start_date && $end_date) {
            $this->db->where('DATE(orders.created_at) >=', $start_date);
            $this->db->where('DATE(orders.created_at) <=', $end_date);
        }

        $this->db->group_by('products.id');
        $this->db->order_by('total_quantity', 'DESC');
        $this->db->limit($limit);

        $query = $this->db->get();
        return $query->result_array();
    }
}
