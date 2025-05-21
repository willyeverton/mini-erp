<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Orders extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Cart_model');
        $this->load->model('Stock_model');
        $this->load->model('Coupon_model');

        // Verificar autenticação
        $this->verify_token();
    }

    public function index_get() {
        // Administradores veem todos os pedidos, clientes veem apenas os seus
        if ($this->user['role'] == 'admin') {
            $orders = $this->Order_model->get_orders();
        } else {
            $orders = $this->Order_model->get_user_orders($this->user['id']);
        }

        $this->response($orders, REST_Controller::HTTP_OK);
    }

    public function view_get($id) {
        $order = $this->Order_model->get_order($id);

        if (!$order) {
            $this->response(['error' => 'Order not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Verificar se o pedido pertence ao usuário ou se é admin
        if ($this->user['role'] != 'admin' && $order['user_id'] != $this->user['id']) {
            $this->response(['error' => 'Permission denied'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        // Adicionar itens do pedido
        $order['items'] = $this->Order_model->get_order_items($id);

        $this->response($order, REST_Controller::HTTP_OK);
    }

    public function create_post() {
        $cart_items = $this->Cart_model->get_cart_items($this->user['id']);

        if (empty($cart_items)) {
            $this->response(['error' => 'Your cart is empty'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $data = $this->post();

        // Validar dados de endereço
        if (!isset($data['zipcode']) || !isset($data['address']) || !isset($data['city']) || !isset($data['state'])) {
            $this->response(['error' => 'Address information is incomplete'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $subtotal = $this->Cart_model->get_cart_total($this->user['id']);

        // Calcular frete
        $shipping = 0;
        if ($subtotal < 200) {
            $shipping = ($subtotal >= 52 && $subtotal <= 166.59) ? 15 : 20;
        }

        // Verificar cupom, se fornecido
        $discount = 0;
        $coupon_id = null;

        if (isset($data['coupon_code'])) {
            $coupon = $this->Coupon_model->validate_coupon($data['coupon_code'], $subtotal);

            if ($coupon) {
                $discount = $this->Coupon_model->calculate_discount($coupon, $subtotal);
                $coupon_id = $coupon['id'];
            }
        }

        // Calcular total
        $total = $subtotal - $discount + $shipping;

        // Verificar estoque novamente antes de finalizar o pedido
        $stock_ok = true;

        foreach ($cart_items as $item) {
            if (!$this->Stock_model->check_stock($item['product_id'], $item['variation_id'], $item['quantity'])) {
                $stock_ok = false;
                break;
            }
        }

        if (!$stock_ok) {
            $this->response(['error' => 'Some items in your cart are no longer in stock'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Criar pedido
        $order_data = array(
            'user_id' => $this->user['id'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'coupon_id' => $coupon_id,
            'zipcode' => $data['zipcode'],
            'address' => $data['address'],
            'number' => isset($data['number']) ? $data['number'] : null,
            'complement' => isset($data['complement']) ? $data['complement'] : null,
            'district' => isset($data['district']) ? $data['district'] : null,
            'city' => $data['city'],
            'state' => $data['state'],
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

        // Limpar carrinho
        $this->Cart_model->clear_cart($this->user['id']);

        // Retornar pedido criado
        $order = $this->Order_model->get_order($order_id);
        $order['items'] = $this->Order_model->get_order_items($order_id);

        $this->response($order, REST_Controller::HTTP_CREATED);
    }

    public function cancel_put($id) {
        $order = $this->Order_model->get_order($id);

        if (!$order) {
            $this->response(['error' => 'Order not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Verificar se o pedido pertence ao usuário ou se é admin
        if ($this->user['role'] != 'admin' && $order['user_id'] != $this->user['id']) {
            $this->response(['error' => 'Permission denied'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        // Verificar se o pedido pode ser cancelado (apenas pedidos pendentes)
        if ($order['status'] != 'pending') {
            $this->response(['error' => 'Only pending orders can be canceled'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Cancelar pedido
        $this->Order_model->update_order($id, array('status' => 'canceled'));

        // Devolver itens ao estoque
        $items = $this->Order_model->get_order_items($id);

        foreach ($items as $item) {
            $this->Stock_model->increase_stock($item['product_id'], $item['variation_id'], $item['quantity']);
        }

        // Retornar pedido atualizado
        $updated_order = $this->Order_model->get_order($id);
        $updated_order['items'] = $items;

        $this->response($updated_order, REST_Controller::HTTP_OK);
    }

    public function update_status_put($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'Permission denied'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $order = $this->Order_model->get_order($id);

        if (!$order) {
            $this->response(['error' => 'Order not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $status = $this->put('status');

        if (!in_array($status, array('pending', 'processing', 'shipped', 'delivered', 'canceled'))) {
            $this->response(['error' => 'Invalid status'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Se estiver cancelando um pedido, devolver itens ao estoque
        if ($status == 'canceled' && $order['status'] != 'canceled') {
            $items = $this->Order_model->get_order_items($id);

            foreach ($items as $item) {
                $this->Stock_model->increase_stock($item['product_id'], $item['variation_id'], $item['quantity']);
            }
        }

        // Se estiver reativando um pedido cancelado, reduzir estoque novamente
        if ($order['status'] == 'canceled' && $status != 'canceled') {
            $items = $this->Order_model->get_order_items($id);

            foreach ($items as $item) {
                // Verificar se há estoque suficiente
                if (!$this->Stock_model->check_stock($item['product_id'], $item['variation_id'], $item['quantity'])) {
                    $this->response(['error' => 'Not enough stock to reactivate this order'], REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }

                $this->Stock_model->decrease_stock($item['product_id'], $item['variation_id'], $item['quantity']);
            }
        }

        $this->Order_model->update_order($id, array('status' => $status));

        // Retornar pedido atualizado
        $updated_order = $this->Order_model->get_order($id);
        $updated_order['items'] = $this->Order_model->get_order_items($id);

        $this->response($updated_order, REST_Controller::HTTP_OK);
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
