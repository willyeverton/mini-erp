<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Cart_model');
        $this->load->model('Stock_model');
        $this->load->model('Coupon_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    public function index() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $data['title'] = 'My Orders';

        // Administradores veem todos os pedidos, clientes veem apenas os seus
        if ($this->user['role'] == 'admin') {
            $data['orders'] = $this->Order_model->get_orders();
        } else {
            $data['orders'] = $this->Order_model->get_user_orders($this->user['id']);
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
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
            $this->session->set_flashdata('error', 'You do not have permission to view this order');
            redirect('orders');
            return;
        }

        $data['title'] = 'Order #' . $id;
        $data['order'] = $order;
        $data['items'] = $this->Order_model->get_order_items($id);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('orders/view', $data);
        $this->load->view('templates/footer');
    }

    public function checkout() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);

        if (empty($cart_items)) {
            $this->session->set_flashdata('error', 'Your cart is empty');
            redirect('cart');
            return;
        }

        $data['title'] = 'Checkout';
        $data['cart_items'] = $cart_items;
        $data['subtotal'] = $this->Cart_model->get_cart_total($this->user['id']);

        // Calcular frete
        $data['shipping'] = 0;
        if ($data['subtotal'] < 200) {
            $data['shipping'] = ($data['subtotal'] >= 52 && $data['subtotal'] <= 166.59) ? 15 : 20;
        }

        // Verificar cupom na sessão
        $data['discount'] = 0;
        if ($this->session->userdata('coupon')) {
            $coupon = $this->session->userdata('coupon');
            $data['coupon'] = $coupon;
            $data['discount'] = $this->Coupon_model->calculate_discount($coupon, $data['subtotal']);
        }

        // Calcular total
        $data['total'] = $data['subtotal'] - $data['discount'] + $data['shipping'];

        $this->form_validation->set_rules('zipcode', 'Zipcode', 'required');
        $this->form_validation->set_rules('address', 'Address', 'required');
        $this->form_validation->set_rules('city', 'City', 'required');
        $this->form_validation->set_rules('state', 'State', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('orders/checkout', $data);
            $this->load->view('templates/footer');
        } else {
            // Verificar estoque novamente antes de finalizar o pedido
            $stock_ok = true;

            foreach ($cart_items as $item) {
                if (!$this->Stock_model->check_stock($item['product_id'], $item['variation_id'], $item['quantity'])) {
                    $stock_ok = false;
                    break;
                }
            }

            if (!$stock_ok) {
                $this->session->set_flashdata('error', 'Some items in your cart are no longer in stock');
                redirect('cart');
                return;
            }

            // Criar pedido
            $order_data = array(
                'user_id' => $this->user['id'],
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'shipping' => $data['shipping'],
                'total' => $data['total'],
                'coupon_id' => isset($coupon['id']) ? $coupon['id'] : null,
                'zipcode' => $this->input->post('zipcode'),
                'address' => $this->input->post('address'),
                'number' => $this->input->post('number'),
                'complement' => $this->input->post('complement'),
                'district' => $this->input->post('district'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'status' => 'pending'
            );

            $order_id = $this->Order_model->create_order($order_data);

            // Adicionar itens ao pedido
            foreach ($cart_items as $item) {
                $item_data = array(
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']
                );

                $this->Order_model->add_order_item($item_data);

                // Atualizar estoque
                $this->Stock_model->decrease_stock($item['product_id'], $item['variation_id'], $item['quantity']);
            }

            // Limpar carrinho e cupom
            $this->Cart_model->clear_cart($this->user['id']);
            $this->session->unset_userdata('coupon');

            $this->session->set_flashdata('success', 'Order placed successfully');
            redirect('orders/success/' . $order_id);
        }
    }

    public function success($order_id) {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $order = $this->Order_model->get_order($order_id);

        if (empty($order) || $order['user_id'] != $this->user['id']) {
            show_404();
        }

        $data['title'] = 'Order Confirmation';
        $data['order'] = $order;
        $data['items'] = $this->Order_model->get_order_items($order_id);

        $this->load->view('templates/header', $data);
        $this->load->view('orders/success', $data);
        $this->load->view('templates/footer');
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
            $this->session->set_flashdata('error', 'You do not have permission to cancel this order');
            redirect('orders');
            return;
        }

        // Verificar se o pedido pode ser cancelado (apenas pedidos pendentes)
        if ($order['status'] != 'pending') {
            $this->session->set_flashdata('error', 'Only pending orders can be canceled');
            redirect('orders/view/' . $id);
            return;
        }

        // Cancelar pedido
        $this->Order_model->update_order($id, array('status' => 'canceled'));

        // Devolver itens ao estoque
        $items = $this->Order_model->get_order_items($id);

        foreach ($items as $item) {
            $this->Stock_model->increase_stock($item['product_id'], $item['variation_id'], $item['quantity']);
        }

        $this->session->set_flashdata('success', 'Order canceled successfully');
        redirect('orders/view/' . $id);
    }

    public function update_status($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            show_404();
        }

        $status = $this->input->post('status');

        if (!in_array($status, array('pending', 'processing', 'shipped', 'delivered', 'canceled'))) {
            $this->session->set_flashdata('error', 'Invalid status');
            redirect('orders/view/' . $id);
            return;
        }

        // Se estiver cancelando um pedido, devolver itens ao estoque
        if ($status == 'canceled' && $order['status'] != 'canceled') {
            $items = $this->Order_model->get_order_items($id);

            foreach ($items as $item) {
                $this->Stock_model->increase_stock($item['product_id'], $item['variation_id'], $item['quantity']);
            }
        }

        // Atualizar status do pedido
        $this->Order_model->update_order($id, array('status' => $status));

        $this->session->set_flashdata('success', 'Order status updated successfully');
        redirect('orders/view/' . $id);
    }

    public function update_tracking($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            show_404();
        }

        $tracking_number = $this->input->post('tracking_number');
        $carrier = $this->input->post('carrier');

        $this->Order_model->update_order($id, array(
            'tracking_number' => $tracking_number,
            'carrier' => $carrier
        ));

        $this->session->set_flashdata('success', 'Tracking information updated successfully');
        redirect('orders/view/' . $id);
    }
}
