<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
    }

    public function index() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        // Administradores podem ver todos os pedidos
        if ($this->user['role'] == 'admin') {
            $orders = $this->Order_model->get_orders();
        } else {
            // Usuários normais só veem seus próprios pedidos
            $orders = $this->Order_model->get_user_orders($this->user['id']);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($orders));
    }

    public function view($id) {
        // Verificar se o usuário está logado
        if (!$this->user) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Order not found']));
            return;
        }

        // Verificar se o pedido pertence ao usuário ou se é admin
        if ($this->user['role'] != 'admin' && $order['user_id'] != $this->user['id']) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Forbidden']));
            return;
        }

        $order['items'] = $this->Order_model->get_order_items($id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($order));
    }

    public function create() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) == 0) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Items are required']));
            return;
        }

        if (!isset($data['address']) || !isset($data['zipcode']) || !isset($data['city']) || !isset($data['state'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Shipping information is required']));
            return;
        }

        // Calcular subtotal e verificar estoque
        $subtotal = 0;
        $items_data = array();

        foreach ($data['items'] as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Product ID and quantity are required for each item']));
                return;
            }

            $product = $this->Product_model->get_product($item['product_id']);

            if (empty($product)) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Product not found: ' . $item['product_id']]));
                return;
            }

            $variation_id = isset($item['variation_id']) ? $item['variation_id'] : null;

            // Verificar estoque
            $stock_available = $this->Stock_model->check_stock($item['product_id'], $variation_id, $item['quantity']);

            if (!$stock_available) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Insufficient stock for product: ' . $product['name']]));
                return;
            }

            $item_subtotal = $product['price'] * $item['quantity'];
            $subtotal += $item_subtotal;

            $items_data[] = array(
                'product_id' => $item['product_id'],
                'variation_id' => $variation_id,
                'quantity' => $item['quantity'],
                'unit_price' => $product['price'],
                'subtotal' => $item_subtotal
            );
        }

        // Calcular frete
        $shipping = 0;
        if ($subtotal < 200) {
            $shipping = ($subtotal >= 52 && $subtotal <= 166.59) ? 15 : 20;
        }

        // Verificar cupom, se houver
        $discount = 0;
        if (isset($data['coupon_code'])) {
            $this->load->model('Coupon_model');
            $coupon = $this->Coupon_model->validate_coupon($data['coupon_code'], $subtotal);

            if ($coupon) {
                $discount = $this->Coupon_model->calculate_discount($coupon, $subtotal);
                $coupon_id = $coupon['id'];
            }
        }

        // Calcular total
        $total = $subtotal - $discount + $shipping;

        // Criar pedido
        $order_data = array(
            'user_id' => $this->user['id'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'coupon_id' => isset($coupon_id) ? $coupon_id : null,
            'zipcode' => $data['zipcode'],
            'address' => $data['address'],
            'number' => isset($data['number']) ? $data['number'] : '',
            'complement' => isset($data['complement']) ? $data['complement'] : '',
            'district' => isset($data['district']) ? $data['district'] : '',
            'city' => $data['city'],
            'state' => $data['state'],
            'status' => 'pending'
        );

        $order_id = $this->Order_model->create_order($order_data);

        // Adicionar itens ao pedido
        foreach ($items_data as $item) {
            $item['order_id'] = $order_id;
            $this->Order_model->add_order_item($item);

            // Atualizar estoque
            $this->Stock_model->decrease_stock(
                $item['product_id'],
                $item['variation_id'],
                $item['quantity']
            );
        }

        $order = $this->Order_model->get_order($order_id);
        $order['items'] = $this->Order_model->get_order_items($order_id);

        $this->output
            ->set_status_header(201)
            ->set_content_type('application/json')
            ->set_output(json_encode($order));
    }

    public function update_status($id) {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Order not found']));
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['status'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Status is required']));
            return;
        }

        $this->Order_model->update_order($id, array('status' => $data['status']));

        $updated_order = $this->Order_model->get_order($id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($updated_order));
    }

    public function cancel($id) {
        // Verificar se o usuário está logado
        if (!$this->user) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $order = $this->Order_model->get_order($id);

        if (empty($order)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Order not found']));
            return;
        }

        // Verificar se o pedido pertence ao usuário ou se é admin
        if ($this->user['role'] != 'admin' && $order['user_id'] != $this->user['id']) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Forbidden']));
            return;
        }

        // Verificar se o pedido pode ser cancelado (apenas pedidos pendentes)
        if ($order['status'] != 'pending') {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Only pending orders can be canceled']));
            return;
        }

        // Cancelar pedido
        $this->Order_model->update_order($id, array('status' => 'canceled'));

        // Devolver itens ao estoque
        $items = $this->Order_model->get_order_items($id);

        foreach ($items as $item) {
            $this->Stock_model->increase_stock(
                $item['product_id'],
                $item['variation_id'],
                $item['quantity']
            );
        }

        $updated_order = $this->Order_model->get_order($id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($updated_order));
    }
}
