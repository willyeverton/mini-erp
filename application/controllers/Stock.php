<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Stock_model');
        $this->load->model('Product_model');
        $this->load->helper('url');
    }

    public function update() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $product_id = $this->input->post('product_id');
        $variation_id = $this->input->post('variation_id');
        $stock_action = $this->input->post('stock_action');
        $stock_quantity = (int)$this->input->post('stock_quantity');

        // Validar dados
        if (!$product_id || $stock_quantity < 0) {
            $this->session->set_flashdata('error', 'Invalid stock data');
            redirect('products/view/' . $product_id);
            return;
        }

        // Obter estoque atual
        $current_stock = $this->Stock_model->get_stock($product_id, $variation_id ?: null);

        // Calcular novo estoque
        $new_stock = $current_stock;

        switch ($stock_action) {
            case 'set':
                $new_stock = $stock_quantity;
                break;
            case 'add':
                $new_stock = $current_stock + $stock_quantity;
                break;
            case 'subtract':
                $new_stock = $current_stock - $stock_quantity;
                if ($new_stock < 0) {
                    $new_stock = 0;
                }
                break;
        }

        // Atualizar estoque
        $this->Stock_model->update_stock($product_id, $variation_id ?: null, $new_stock);

        $this->session->set_flashdata('success', 'Stock updated successfully');
        redirect('products/view/' . $product_id);
    }
}
