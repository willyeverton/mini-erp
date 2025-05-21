<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->helper('url');
    }

    public function index() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $data['title'] = 'Dashboard';

        // Estatísticas para administradores
        if ($this->user['role'] == 'admin') {
            // Total de vendas hoje
            $today = date('Y-m-d');
            $data['today_sales'] = $this->Order_model->get_sales_report($today, $today);

            // Total de vendas este mês
            $first_day_month = date('Y-m-01');
            $data['month_sales'] = $this->Order_model->get_sales_report($first_day_month, $today);

            // Produtos mais vendidos
            $data['top_products'] = $this->Order_model->get_top_products(5);

            // Produtos com estoque baixo
            $data['low_stock_products'] = $this->get_low_stock_products();

            // Pedidos recentes
            $data['recent_orders'] = $this->get_recent_orders(5);
        } else {
            // Para usuários normais, mostrar apenas seus pedidos recentes
            $data['user_orders'] = $this->Order_model->get_user_orders($this->user['id']);
        }

        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }

    public function reports() {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            show_error('You are not authorized to access this page', 403);
        }

        $data['title'] = 'Sales Reports';

        // Filtros de data
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-01');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d');

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        // Relatório de vendas
        $data['sales_report'] = $this->Order_model->get_sales_report($start_date, $end_date);

        // Produtos mais vendidos
        $data['top_products'] = $this->Order_model->get_top_products(10, $start_date, $end_date);

        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/reports', $data);
        $this->load->view('templates/footer');
    }

    private function get_low_stock_products($threshold = 5) {
        $products = $this->Product_model->get_products();
        $low_stock = array();

        foreach ($products as $product) {
            $variations = $this->Product_model->get_variations($product['id']);

            if (count($variations) > 0) {
                foreach ($variations as $variation) {
                    $stock = $this->Stock_model->get_stock($product['id'], $variation['id']);

                    if ($stock <= $threshold) {
                        $low_stock[] = array(
                            'product_id' => $product['id'],
                            'product_name' => $product['name'],
                            'variation_id' => $variation['id'],
                            'variation_name' => $variation['name'],
                            'stock' => $stock
                        );
                    }
                }
            } else {
                $stock = $this->Stock_model->get_stock($product['id']);

                if ($stock <= $threshold) {
                    $low_stock[] = array(
                        'product_id' => $product['id'],
                        'product_name' => $product['name'],
                        'variation_id' => null,
                        'variation_name' => '',
                        'stock' => $stock
                    );
                }
            }
        }

        return $low_stock;
    }

    private function get_recent_orders($limit = 5) {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get('orders');
        return $query->result_array();
    }
}
