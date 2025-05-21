<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Cart extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Cart_model');
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->model('Coupon_model');

        // Verificar autenticação para todos os métodos
        $this->verify_token();
    }

    public function index_get() {
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        // Calcular frete
        $shipping = 0;
        if ($subtotal < 200) {
            $shipping = ($subtotal >= 52 && $subtotal <= 166.59) ? 15 : 20;
        }

        // Verificar cupom na requisição
        $coupon_code = $this->get('coupon');
        $discount = 0;
        $coupon = null;

        if ($coupon_code) {
            $coupon = $this->Coupon_model->get_coupon_by_code($coupon_code);
            if ($coupon) {
                $discount = $this->Coupon_model->calculate_discount($coupon, $subtotal);
            }
        }

        // Calcular total
        $total = $subtotal - $discount + $shipping;

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $coupon
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function add_post() {
        $data = $this->post();

        // Validação básica
        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            $this->response(['error' => 'Product ID and quantity are required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $product_id = $data['product_id'];
        $variation_id = isset($data['variation_id']) ? $data['variation_id'] : null;
        $quantity = $data['quantity'];

        // Verificar se o produto existe
        $product = $this->Product_model->get_product($product_id);

        if (!$product) {
            $this->response(['error' => 'Product not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Verificar variação, se fornecida
        if ($variation_id) {
            $variation = $this->Product_model->get_variation($variation_id);

            if (!$variation || $variation['product_id'] != $product_id) {
                $this->response(['error' => 'Invalid variation'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }
        }

        // Verificar estoque disponível
        if (!$this->Stock_model->check_stock($product_id, $variation_id, $quantity)) {
            $this->response(['error' => 'Not enough stock available'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Adicionar ao carrinho
        $this->Cart_model->add_to_cart($this->user['id'], $product_id, $variation_id, $quantity, $product['price']);

        // Retornar carrinho atualizado
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function update_put($id) {
        $data = $this->put();

        // Validação básica
        if (!isset($data['quantity'])) {
            $this->response(['error' => 'Quantity is required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $quantity = $data['quantity'];

        // Verificar se o item existe no carrinho
        $cart_item = $this->Cart_model->get_cart_item($id);

        if (!$cart_item || $cart_item['user_id'] != $this->user['id']) {
            $this->response(['error' => 'Cart item not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Se a quantidade for 0, remover o item
        if ($quantity <= 0) {
            $this->Cart_model->remove_from_cart($id);

            // Retornar carrinho atualizado
            $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
            $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

            $response = array(
                'items' => $cart_items,
                'subtotal' => $subtotal
            );

            $this->response($response, REST_Controller::HTTP_OK);
            return;
        }

        // Verificar estoque disponível
        if (!$this->Stock_model->check_stock($cart_item['product_id'], $cart_item['variation_id'], $quantity)) {
            $this->response(['error' => 'Not enough stock available'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Atualizar quantidade
        $this->Cart_model->update_cart($id, $quantity);

        // Retornar carrinho atualizado
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function remove_delete($id) {
        // Verificar se o item existe no carrinho
        $cart_item = $this->Cart_model->get_cart_item($id);

        if (!$cart_item || $cart_item['user_id'] != $this->user['id']) {
            $this->response(['error' => 'Cart item not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Remover do carrinho
        $this->Cart_model->remove_from_cart($id);

        // Retornar carrinho atualizado
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function clear_delete() {
        // Limpar carrinho
        $this->Cart_model->clear_cart($this->user['id']);

        $this->response(['success' => 'Cart cleared successfully'], REST_Controller::HTTP_OK);
    }

    public function apply_coupon_post() {
        $coupon_code = $this->post('coupon_code');

        if (!$coupon_code) {
            $this->response(['error' => 'Coupon code is required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);
        $coupon = $this->Coupon_model->validate_coupon($coupon_code, $subtotal);

        if (!$coupon) {
            $this->response(['error' => 'Invalid or expired coupon code'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $discount = $this->Coupon_model->calculate_discount($coupon, $subtotal);

        // Calcular frete
        $shipping = 0;
        if ($subtotal < 200) {
            $shipping = ($subtotal >= 52 && $subtotal <= 166.59) ? 15 : 20;
        }

        $response = array(
            'coupon' => $coupon,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal - $discount + $shipping
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    private function verify_token() {
        $headers = $this->input->request_headers();

        if (!isset($headers['Authorization'])) {
            $this->response(['error' => 'Authorization header not found'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        $token_parts = explode(' ', $headers['Authorization']);

        if (count($token_parts) != 2 || $token_parts[0] != 'Bearer') {
            $this->response(['error' => 'Invalid authorization format'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        $this->load->model('OAuth_model');
        $token_data = $this->OAuth_model->validate_token($token_parts[1]);

        if (!$token_data) {
            $this->response(['error' => 'Invalid or expired token'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        $this->load->model('User_model');
        $this->user = $this->User_model->get_user_by_id($token_data['user_id']);

        if (!$this->user) {
            $this->response(['error' => 'User not found'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }
}
