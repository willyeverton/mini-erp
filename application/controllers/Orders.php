<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->helper('url');
    }

    public function index() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        // Administradores podem ver todos os pedidos
        if ($this->user['role'] == 'admin') {
            $data['orders'] = $this->Order_model->get_orders();
        } else {
            // Usuários normais só veem seus próprios pedidos
            $data['orders'] = $this->Order_model->get_user_orders($this->user['id']);
        }

        $data['title'] = 'My Orders';

        $this->load->view('templates/header', $data);
        $this->load->view('orders/index', $data);
        $this->load->view('templates/footer');
    }

    public function view($id) {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            show_404();
        }

        // Verificar se o pedido pertence ao usuário ou se é admin
        if ($this->user['role'] != 'admin' && $order['user_id'] != $this->user['id']) {
            show_error('You are not authorized to view this order', 403);
        }

        $data['order'] = $order;
        $data['items'] = $this->Order_model->get_order_items($id);
        $data['title'] = 'Order #' . $id;

        $this->load->view('templates/header', $data);
        $this->load->view('orders/view', $data);
        $this->load->view('templates/footer');
    }

    public function success($id) {
        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            show_404();
        }

        $data['order'] = $order;
        $data['title'] = 'Order Confirmation';

        $this->load->view('templates/header', $data);
        $this->load->view('orders/success', $data);
        $this->load->view('templates/footer');
    }

    public function update_status($id) {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            show_error('You are not authorized to perform this action', 403);
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            show_404();
        }

        $status = $this->input->post('status');

        if ($status) {
            $this->Order_model->update_order($id, array('status' => $status));
            $this->session->set_flashdata('success', 'Order status updated successfully');
        }

        redirect('orders/view/' . $id);
    }

    public function cancel($id) {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            show_404();
        }

        // Verificar se o pedido pertence ao usuário ou se é admin
        if ($this->user['role'] != 'admin' && $order['user_id'] != $this->user['id']) {
            show_error('You are not authorized to cancel this order', 403);
        }

        // Verificar se o pedido pode ser cancelado (apenas pedidos pendentes)
        if ($order['status'] != 'pending') {
            $this->session->set_flashdata('error', 'Only pending orders can be canceled');
            redirect('orders/view/' . $id);
        }

        // Cancelar pedido
        $this->Order_model->update_order($id, array('status' => 'canceled'));

        // Devolver itens ao estoque
        $items = $this->Order_model->get_order_items($id);
        $this->load->model('Stock_model');

        foreach ($items as $item) {
            $this->Stock_model->increase_stock(
                $item['product_id'],
                $item['variation_id'],
                $item['quantity']
            );
        }

        $this->session->set_flashdata('success', 'Order canceled successfully');
        redirect('orders/view/' . $id);
    }
}
