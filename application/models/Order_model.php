<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_orders() {
        $this->db->select('orders.*, users.name as user_name');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id', 'left');
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
        $this->db->select('orders.*, users.name as user_name, users.email as user_email');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id', 'left');
        $this->db->where('orders.id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_order_items($order_id) {
        $this->db->select('order_items.*, products.name as product_name, product_variations.name as variation_name');
        $this->db->from('order_items');
        $this->db->join('products', 'products.id = order_items.product_id', 'left');
        $this->db->join('product_variations', 'product_variations.id = order_items.variation_id', 'left');
        $this->db->where('order_id', $order_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function create_order($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }

    public function add_order_item($data) {
        $this->db->insert('order_items', $data);
        return $this->db->insert_id();
    }

    public function update_order($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('orders', $data);
    }

    public function count_orders($start_date = null, $end_date = null) {
        if ($start_date && $end_date) {
            $this->db->where('DATE(created_at) >=', $start_date);
            $this->db->where('DATE(created_at) <=', $end_date);
        }
        return $this->db->count_all_results('orders');
    }

    public function count_orders_by_status($status, $start_date = null, $end_date = null) {
        $this->db->where('status', $status);

        if ($start_date && $end_date) {
            $this->db->where('DATE(created_at) >=', $start_date);
            $this->db->where('DATE(created_at) <=', $end_date);
        }

        return $this->db->count_all_results('orders');
    }

    public function sum_revenue($start_date = null, $end_date = null) {
        $this->db->select_sum('total');

        if ($start_date && $end_date) {
            $this->db->where('DATE(created_at) >=', $start_date);
            $this->db->where('DATE(created_at) <=', $end_date);
        }

        // Excluir pedidos cancelados
        $this->db->where('status !=', 'canceled');

        $query = $this->db->get('orders');
        $result = $query->row_array();

        return $result['total'] ? $result['total'] : 0;
    }

    public function get_daily_sales($start_date, $end_date) {
        $this->db->select('DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue');
        $this->db->where('DATE(created_at) >=', $start_date);
        $this->db->where('DATE(created_at) <=', $end_date);
        $this->db->where('status !=', 'canceled');
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('DATE(created_at)', 'ASC');

        $query = $this->db->get('orders');
        return $query->result_array();
    }

    // Renomear para manter consistência
    public function get_top_selling_products($start_date, $end_date, $limit = 5) {
        $this->db->select('order_items.product_id, products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as total_revenue');
        $this->db->from('order_items');
        $this->db->join('orders', 'orders.id = order_items.order_id');
        $this->db->join('products', 'products.id = order_items.product_id');
        $this->db->where('DATE(orders.created_at) >=', $start_date);
        $this->db->where('DATE(orders.created_at) <=', $end_date);
        $this->db->where('orders.status !=', 'canceled');
        $this->db->group_by('order_items.product_id');
        $this->db->order_by('total_revenue', 'DESC');
        $this->db->limit($limit);

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obter relatório de vendas para um período específico
     */
    public function get_sales_report($start_date, $end_date) {
        // Obter todos os dias no intervalo
        $days = [];
        $current = strtotime($start_date);
        $end = strtotime($end_date);

        while ($current <= $end) {
            $days[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        $result = [];

        foreach ($days as $day) {
            // Contar pedidos neste dia
            $this->db->where('DATE(created_at)', $day);
            $this->db->where('status !=', 'canceled');
            $orders_count = $this->db->count_all_results('orders');

            // Calcular receita neste dia
            $this->db->select_sum('total');
            $this->db->where('DATE(created_at)', $day);
            $this->db->where('status !=', 'canceled');
            $query = $this->db->get('orders');
            $revenue = $query->row()->total ?: 0;

            $result[] = [
                'date' => $day,
                'orders' => $orders_count,
                'revenue' => $revenue
            ];
        }

        return $result;
    }

    /**
     * Obter contagem de pedidos por status
     */
    public function get_order_status_counts() {
        $this->db->select('status, COUNT(*) as count');
        $this->db->from('orders');
        $this->db->group_by('status');
        $query = $this->db->get();

        return $query->result_array();
    }
}
