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

        // Verificar autenticação
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

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function add_post() {
        $product_id = $this->post('product_id');
        $quantity = $this->post('quantity') ? $this->post('quantity') : 1;
        $variation_id = $this->post('variation_id') ? $this->post('variation_id') : null;

        if (!$product_id) {
            $this->response(['error' => 'Product ID is required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

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
        $this->Cart_model->add_to_cart($this->user['id'], $product_id, $quantity, $variation_id);

        // Retornar carrinho atualizado
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        // Calcular frete
        $shipping = 0;
        if ($subtotal < 200) {
            $shipping = ($subtotal >= 52 && $subtotal <= 166.59) ? 15 : 20;
        }

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function update_put($cart_id) {
        $quantity = $this->put('quantity');

        if ($quantity === null) {
            $this->response(['error' => 'Quantity is required'], REST_Controller::HTTP_BAD_REQUEST);
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
            $this->response(['error' => 'Cart item not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Verificar estoque disponível
        if (!$this->Stock_model->check_stock($item_data['product_id'], $item_data['variation_id'], $quantity)) {
            $this->response(['error' => 'Not enough stock available'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $this->Cart_model->update_cart_item($cart_id, $quantity);

        // Retornar carrinho atualizado
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        // Calcular frete
        $shipping = 0;
        if ($subtotal < 200) {
            $shipping = ($subtotal >= 52 && $subtotal <= 166.59) ? 15 : 20;
        }

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function delete_delete($cart_id) {
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
            $this->response(['error' => 'Cart item not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $this->Cart_model->remove_from_cart($cart_id);

        // Retornar carrinho atualizado
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);
        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        // Calcular frete
        $shipping = 0;
        if ($subtotal < 200) {
            $shipping = ($subtotal >= 52 && $subtotal <= 166.59) ? 15 : 20;
        }

        $response = array(
            'items' => $cart_items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping
        );

        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function clear_delete() {
        $this->Cart_model->clear_cart($this->user['id']);

        $response = array(
            'items' => array(),
            'subtotal' => 0,
            'shipping' => 0,
            'total' => 0
        );

        $this->response($response, REST_Controller::HTTP_OK);
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
            $this->response(['error' => 'Authorization header required'], REST_Controller::HTTP_UNAUTHORIZED);
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
