<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->model('Coupon_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    public function index() {
        $data['title'] = 'Shopping Cart';
        $data['cart'] = $this->session->userdata('cart') ? $this->session->userdata('cart') : array();

        // Calcular subtotal
        $subtotal = 0;
        foreach ($data['cart'] as $item) {
            $subtotal += $item['subtotal'];
        }
        $data['subtotal'] = $subtotal;

        // Calcular frete
        $data['shipping'] = $this->calculate_shipping($subtotal);

        // Verificar cupom
        $coupon = $this->session->userdata('coupon');
        $data['coupon'] = $coupon;

        if ($coupon) {
            $data['discount'] = $this->Coupon_model->calculate_discount($coupon, $subtotal);
        } else {
            $data['discount'] = 0;
        }

        // Calcular total
        $data['total'] = $subtotal - $data['discount'] + $data['shipping'];

        $this->load->view('templates/header', $data);
        $this->load->view('cart/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $product_id = $this->input->post('product_id');
        $variation_id = $this->input->post('variation_id');
        $quantity = $this->input->post('quantity') ? $this->input->post('quantity') : 1;

        $product = $this->Product_model->get_product($product_id);

        if (empty($product)) {
            $this->session->set_flashdata('error', 'Product not found');
            redirect('products');
        }

        // Verificar estoque
        $stock_available = $this->Stock_model->check_stock($product_id, $variation_id, $quantity);

        if (!$stock_available) {
            $this->session->set_flashdata('error', 'Insufficient stock');
            redirect('products/view/' . $product_id);
        }

        // Obter carrinho atual
        $cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : array();

        // Gerar chave única para o item (produto + variação)
        $item_key = $product_id . '-' . ($variation_id ? $variation_id : '0');

        // Se o item já existe no carrinho, atualizar quantidade
        if (isset($cart[$item_key])) {
            $cart[$item_key]['quantity'] += $quantity;
            $cart[$item_key]['subtotal'] = $cart[$item_key]['price'] * $cart[$item_key]['quantity'];
        } else {
            // Adicionar novo item ao carrinho
            $variation_name = '';
            if ($variation_id) {
                $variations = $this->Product_model->get_variations($product_id);
                foreach ($variations as $var) {
                    if ($var['id'] == $variation_id) {
                        $variation_name = $var['name'];
                        break;
                    }
                }
            }

            $cart[$item_key] = array(
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'name' => $product['name'],
                'variation_name' => $variation_name,
                'price' => $product['price'],
                'quantity' => $quantity,
                'subtotal' => $product['price'] * $quantity
            );
        }

        // Atualizar carrinho na sessão
        $this->session->set_userdata('cart', $cart);

        $this->session->set_flashdata('success', 'Product added to cart');
        redirect('cart');
    }

    public function update() {
        $cart = $this->session->userdata('cart');
        $quantities = $this->input->post('quantity');

        if ($cart && $quantities) {
            foreach ($quantities as $key => $qty) {
                if (isset($cart[$key])) {
                    // Verificar estoque
                    $stock_available = $this->Stock_model->check_stock(
                        $cart[$key]['product_id'],
                        $cart[$key]['variation_id'],
                        $qty
                    );

                    if (!$stock_available) {
                        $this->session->set_flashdata('error', 'Insufficient stock for ' . $cart[$key]['name']);
                        redirect('cart');
                    }

                    if ($qty > 0) {
                        $cart[$key]['quantity'] = $qty;
                        $cart[$key]['subtotal'] = $cart[$key]['price'] * $qty;
                    } else {
                        unset($cart[$key]);
                    }
                }
            }

            $this->session->set_userdata('cart', $cart);
            $this->session->set_flashdata('success', 'Cart updated successfully');
        }

        redirect('cart');
    }

    public function remove($key) {
        $cart = $this->session->userdata('cart');

        if (isset($cart[$key])) {
            unset($cart[$key]);
            $this->session->set_userdata('cart', $cart);
            $this->session->set_flashdata('success', 'Item removed from cart');
        }

        redirect('cart');
    }

    public function clear() {
        $this->session->unset_userdata('cart');
        $this->session->unset_userdata('coupon');
        $this->session->set_flashdata('success', 'Cart cleared successfully');
        redirect('cart');
    }

    public function apply_coupon() {
        $code = $this->input->post('coupon_code');

        if (!$code) {
            $this->session->set_flashdata('error', 'Please enter a coupon code');
            redirect('cart');
        }

        $cart = $this->session->userdata('cart');

        if (!$cart || count($cart) == 0) {
            $this->session->set_flashdata('error', 'Your cart is empty');
            redirect('cart');
        }

        // Calcular subtotal
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['subtotal'];
        }

        // Validar cupom
        $coupon = $this->Coupon_model->validate_coupon($code, $subtotal);

        if (!$coupon) {
            $this->session->set_flashdata('error', 'Invalid or expired coupon code');
            redirect('cart');
        }

        // Armazenar cupom na sessão
        $this->session->set_userdata('coupon', $coupon);
        $this->session->set_flashdata('success', 'Coupon applied successfully');
        redirect('cart');
    }

    public function remove_coupon() {
        $this->session->unset_userdata('coupon');
        $this->session->set_flashdata('success', 'Coupon removed successfully');
        redirect('cart');
    }

    public function checkout() {
        $cart = $this->session->userdata('cart');

        if (!$cart || count($cart) == 0) {
            $this->session->set_flashdata('error', 'Your cart is empty');
            redirect('cart');
        }

        $data['title'] = 'Checkout';
        $data['cart'] = $cart;

        // Calcular subtotal
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['subtotal'];
        }
        $data['subtotal'] = $subtotal;

        // Calcular frete
        $data['shipping'] = $this->calculate_shipping($subtotal);

        // Verificar cupom
        $coupon = $this->session->userdata('coupon');
        $data['coupon'] = $coupon;

        if ($coupon) {
            $data['discount'] = $this->Coupon_model->calculate_discount($coupon, $subtotal);
        } else {
            $data['discount'] = 0;
        }

        // Calcular total
        $data['total'] = $subtotal - $data['discount'] + $data['shipping'];

        $this->form_validation->set_rules('zipcode', 'ZIP Code', 'required');
        $this->form_validation->set_rules('address', 'Address', 'required');
        $this->form_validation->set_rules('number', 'Number', 'required');
        $this->form_validation->set_rules('district', 'District', 'required');
        $this->form_validation->set_rules('city', 'City', 'required');
        $this->form_validation->set_rules('state', 'State', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('cart/checkout', $data);
            $this->load->view('templates/footer');
        } else {
            // Criar pedido
            $order_data = array(
                'user_id' => $this->user ? $this->user['id'] : null,
                'subtotal' => $subtotal,
                'discount' => $data['discount'],
                'shipping' => $data['shipping'],
                'total' => $data['total'],
                'coupon_id' => $coupon ? $coupon['id'] : null,
                'zipcode' => $this->input->post('zipcode'),
                'address' => $this->input->post('address'),
                'number' => $this->input->post('number'),
                'complement' => $this->input->post('complement'),
                'district' => $this->input->post('district'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state')
            );

            $this->load->model('Order_model');
            $order_id = $this->Order_model->create_order($order_data);

            // Adicionar itens ao pedido
            foreach ($cart as $item) {
                $item_data = array(
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ? $item['variation_id'] : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['subtotal']
                );

                $this->Order_model->add_order_item($item_data);

                // Atualizar estoque
                $this->Stock_model->decrease_stock(
                    $item['product_id'],
                    $item['variation_id'],
                    $item['quantity']
                );
            }

            // Enviar email de confirmação
            $this->send_order_email($order_id);

            // Limpar carrinho e cupom
            $this->session->unset_userdata('cart');
            $this->session->unset_userdata('coupon');

            $this->session->set_flashdata('success', 'Order placed successfully');
            redirect('orders/success/' . $order_id);
        }
    }

    private function calculate_shipping($subtotal) {
        if ($subtotal >= 200) {
            return 0; // Frete grátis
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15; // Frete R$15
        } else {
            return 20; // Frete R$20
        }
    }

    private function send_order_email($order_id) {
        $this->load->model('Order_model');
        $order = $this->Order_model->get_order($order_id);
        $items = $this->Order_model->get_order_items($order_id);

        $this->load->library('email');

        $config['mailtype'] = 'html';
        $this->email->initialize($config);

        $this->email->from('noreply@minierp.com', 'Mini ERP');
        $this->email->to($this->user ? $this->user['email'] : 'customer@example.com');

        $this->email->subject('Order Confirmation #' . $order_id);

        $message = '<h1>Order Confirmation</h1>';
        $message .= '<p>Thank you for your order. Your order details are below:</p>';
        $message .= '<h2>Order #' . $order_id . '</h2>';
        $message .= '<p><strong>Date:</strong> ' . date('Y-m-d H:i:s', strtotime($order['created_at'])) . '</p>';
        $message .= '<p><strong>Status:</strong> ' . ucfirst($order['status']) . '</p>';

        $message .= '<h3>Items</h3>';
        $message .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $message .= '<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr>';

        foreach ($items as $item) {
            $message .= '<tr>';
            $message .= '<td>' . $item['product_name'] . ($item['variation_name'] ? ' - ' . $item['variation_name'] : '') . '</td>';
            $message .= '<td>$' . number_format($item['unit_price'], 2) . '</td>';
            $message .= '<td>' . $item['quantity'] . '</td>';
            $message .= '<td>$' . number_format($item['subtotal'], 2) . '</td>';
            $message .= '</tr>';
        }

        $message .= '</table>';

        $message .= '<h3>Summary</h3>';
        $message .= '<p><strong>Subtotal:</strong> $' . number_format($order['subtotal'], 2) . '</p>';

        if ($order['discount'] > 0) {
            $message .= '<p><strong>Discount:</strong> $' . number_format($order['discount'], 2) . '</p>';
        }

        $message .= '<p><strong>Shipping:</strong> $' . number_format($order['shipping'], 2) . '</p>';
        $message .= '<p><strong>Total:</strong> $' . number_format($order['total'], 2) . '</p>';

        $message .= '<h3>Shipping Address</h3>';
        $message .= '<p>' . $order['address'] . ', ' . $order['number'] . '</p>';
        if ($order['complement']) {
            $message .= '<p>' . $order['complement'] . '</p>';
        }
        $message .= '<p>' . $order['district'] . '</p>';
        $message .= '<p>' . $order['city'] . ' - ' . $order['state'] . '</p>';
        $message .= '<p>ZIP: ' . $order['zipcode'] . '</p>';

        $this->email->message($message);

        $this->email->send();
    }
}
