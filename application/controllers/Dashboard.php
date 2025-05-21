<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->model('User_model');
        $this->load->helper('url');
    }

    public function index() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $data['title'] = 'Dashboard';

        // Dados diferentes para administradores e clientes
        if ($this->user['role'] == 'admin') {
            // Estatísticas para administradores

            // Período padrão: últimos 30 dias
            $end_date = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime('-30 days'));

            // Permitir filtrar por período
            if ($this->input->get('start_date') && $this->input->get('end_date')) {
                $start_date = $this->input->get('start_date');
                $end_date = $this->input->get('end_date');
            }

            $data['start_date'] = $start_date;
            $data['end_date'] = $end_date;

            // Relatório de vendas
            $data['sales_report'] = $this->Order_model->get_sales_report($start_date, $end_date);

            // Calcular totais
            $total_orders = 0;
            $total_revenue = 0;

            foreach ($data['sales_report'] as $day) {
                $total_orders += $day['orders'];
                $total_revenue += $day['revenue'];
            }

            $data['total_orders'] = $total_orders;
            $data['total_revenue'] = $total_revenue;

            // Produtos mais vendidos
            $data['top_products'] = $this->Order_model->get_top_products(5, $start_date, $end_date);

            // Produtos com estoque baixo
            $data['low_stock_products'] = $this->Stock_model->get_low_stock_products(5);

            // Pedidos recentes
            $data['recent_orders'] = array_slice($this->Order_model->get_orders(), 0, 5);

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('dashboard/admin', $data);
            $this->load->view('templates/footer');
        } else {
            // Dashboard para clientes
            $data['recent_orders'] = array_slice($this->Order_model->get_user_orders($this->user['id']), 0, 5);

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('dashboard/customer', $data);
            $this->load->view('templates/footer');
        }
    }

    public function reports() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $data['title'] = 'Sales Reports';

        // Período padrão: últimos 30 dias
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-30 days'));

        // Permitir filtrar por período
        if ($this->input->post('start_date') && $this->input->post('end_date')) {
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
        }

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        // Relatório de vendas
        $data['sales_report'] = $this->Order_model->get_sales_report($start_date, $end_date);

        // Calcular totais
        $total_orders = 0;
        $total_revenue = 0;

        foreach ($data['sales_report'] as $day) {
            $total_orders += $day['orders'];
            $total_revenue += $day['revenue'];
        }

        $data['total_orders'] = $total_orders;
        $data['total_revenue'] = $total_revenue;

        // Produtos mais vendidos
        $data['top_products'] = $this->Order_model->get_top_products(10, $start_date, $end_date);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('dashboard/reports', $data);
        $this->load->view('templates/footer');
    }

    public function inventory() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $data['title'] = 'Inventory Management';

        // Obter todos os produtos com informações de estoque
        $data['products'] = $this->Product_model->get_products();

        foreach ($data['products'] as &$product) {
            $variations = $this->Product_model->get_variations($product['id']);

            if (count($variations) > 0) {
                $product['variations'] = $variations;

                foreach ($product['variations'] as &$variation) {
                    $variation['stock'] = $this->Stock_model->get_stock($product['id'], $variation['id']);
                }
            } else {
                $product['stock'] = $this->Stock_model->get_stock($product['id']);
            }
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('dashboard/inventory', $data);
        $this->load->view('templates/footer');
    }

    public function update_stock() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $product_id = $this->input->post('product_id');
        $variation_id = $this->input->post('variation_id') ? $this->input->post('variation_id') : null;
        $quantity = $this->input->post('quantity');

        if (!$product_id || $quantity === null) {
            $this->session->set_flashdata('error', 'Invalid request');
            redirect('dashboard/inventory');
            return;
        }

        $this->Stock_model->update_stock($product_id, $variation_id, $quantity);

        $this->session->set_flashdata('success', 'Stock updated successfully');
        redirect('dashboard/inventory');
    }
}
