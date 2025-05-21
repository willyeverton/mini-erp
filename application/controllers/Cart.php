<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Cart_model');
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->model('Coupon_model');
        $this->load->helper('url');
    }

    public function index() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $data['title'] = 'Shopping Cart';
        $data['cart_items'] = $this->Cart_model->get_cart_items($this->user['id']);
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

        $this->load->view('templates/header', $data);
        $this->load->view('cart/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $product_id = $this->input->post('product_id');
        $quantity = $this->input->post('quantity') ? $this->input->post('quantity') : 1;
        $variation_id = $this->input->post('variation_id') ? $this->input->post('variation_id') : null;

        if (!$product_id) {
            $this->session->set_flashdata('error', 'Invalid product');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        // Verificar se o produto existe
        $product = $this->Product_model->get_product($product_id);

        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        // Verificar variação, se fornecida
        if ($variation_id) {
            $variation = $this->Product_model->get_variation($variation_id);

            if (!$variation || $variation['product_id'] != $product_id) {
                $this->session->set_flashdata('error', 'Invalid variation');
                redirect($_SERVER['HTTP_REFERER']);
                return;
            }
        }

        // Verificar estoque disponível
        if (!$this->Stock_model->check_stock($product_id, $variation_id, $quantity)) {
            $this->session->set_flashdata('error', 'Not enough stock available');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        // Adicionar ao carrinho
        $this->Cart_model->add_to_cart($this->user['id'], $product_id, $quantity, $variation_id);

        $this->session->set_flashdata('success', 'Product added to cart');
        redirect($_SERVER['HTTP_REFERER']);
    }


    public function update() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $cart_id = $this->input->post('cart_id');
        $quantity = $this->input->post('quantity');

        if (!$cart_id || $quantity === null) {
            $this->session->set_flashdata('error', 'Invalid request');
            redirect('cart');
            return;
        }

        // Verificar se o item pertence ao usuário
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $item_belongs_to_user = false;
        $item_data = null;

        foreach ($cart_items as $item) {
            if ($item['id'] == $cart_id) {
                $item_belongs_to_user = true;
                $item_data = $item;
                break;
            }
        }

        if (!$item_belongs_to_user) {
            $this->session->set_flashdata('error', 'Invalid cart item');
            redirect('cart');
            return;
        }

        // Verificar estoque disponível
        if (!$this->Stock_model->check_stock($item_data['product_id'], $item_data['variation_id'], $quantity)) {
            $this->session->set_flashdata('error', 'Not enough stock available');
            redirect('cart');
            return;
        }

        $this->Cart_model->update_cart_item($cart_id, $quantity);

        $this->session->set_flashdata('success', 'Cart updated');
        redirect('cart');
    }


    public function delete($cart_id) {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        // Verificar se o item pertence ao usuário
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $item_belongs_to_user = false;

        foreach ($cart_items as $item) {
            if ($item['id'] == $cart_id) {
                $item_belongs_to_user = true;
                break;
            }
        }

        if (!$item_belongs_to_user) {
            $this->session->set_flashdata('error', 'Invalid cart item');
            redirect('cart');
            return;
        }

        $this->Cart_model->remove_from_cart($cart_id);

        $this->session->set_flashdata('success', 'Item removed from cart');
        redirect('cart');
    }

    public function clear() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $this->Cart_model->clear_cart($this->user['id']);

        $this->session->set_flashdata('success', 'Cart cleared');
        redirect('cart');
    }

    public function apply_coupon() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $coupon_code = $this->input->post('coupon_code');

        if (!$coupon_code) {
            $this->session->set_flashdata('error', 'Please enter a coupon code');
            redirect('cart');
            return;
        }

        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);
        $coupon = $this->Coupon_model->validate_coupon($coupon_code, $subtotal);

        if (!$coupon) {
            $this->session->set_flashdata('error', 'Invalid or expired coupon code');
            redirect('cart');
            return;
        }

        // Salvar cupom na sessão
        $this->session->set_userdata('coupon', $coupon);

        $this->session->set_flashdata('success', 'Coupon applied successfully');
        redirect('cart');
    }

    public function remove_coupon() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $this->session->unset_userdata('coupon');

        $this->session->set_flashdata('success', 'Coupon removed');
        redirect('cart');
    }
}
