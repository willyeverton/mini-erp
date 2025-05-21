<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Product_model');
    }

    public function get_cart_items($user_id) {
        $this->db->select('cart.*, products.name as product_name, products.price, products.image, product_variations.name as variation_name');
        $this->db->from('cart');
        $this->db->join('products', 'products.id = cart.product_id');
        $this->db->join('product_variations', 'product_variations.id = cart.variation_id', 'left');
        $this->db->where('cart.user_id', $user_id);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function add_to_cart($user_id, $product_id, $variation_id = null, $quantity = 1) {
        // Verificar se o produto já está no carrinho
        $this->db->where('user_id', $user_id);
        $this->db->where('product_id', $product_id);

        if ($variation_id) {
            $this->db->where('variation_id', $variation_id);
        } else {
            $this->db->where('variation_id IS NULL', null, false);
        }

        $query = $this->db->get('cart');

        if ($query->num_rows() > 0) {
            // Atualizar quantidade
            $cart_item = $query->row_array();
            $new_quantity = $cart_item['quantity'] + $quantity;

            $this->db->where('id', $cart_item['id']);
            $this->db->update('cart', ['quantity' => $new_quantity]);

            return $cart_item['id'];
        } else {
            // Adicionar novo item
            $data = [
                'user_id' => $user_id,
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $quantity
            ];

            $this->db->insert('cart', $data);
            return $this->db->insert_id();
        }
    }

    public function update_cart_item($cart_id, $quantity) {
        $this->db->where('id', $cart_id);
        return $this->db->update('cart', ['quantity' => $quantity]);
    }

    public function remove_cart_item($cart_id) {
        $this->db->where('id', $cart_id);
        return $this->db->delete('cart');
    }

    public function clear_cart($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->delete('cart');
    }

    public function get_cart_total($user_id) {
        $this->db->select_sum('products.price * cart.quantity', 'total');
        $this->db->from('cart');
        $this->db->join('products', 'products.id = cart.product_id');
        $this->db->where('cart.user_id', $user_id);

        $query = $this->db->get();
        $result = $query->row_array();

        return $result['total'] ?: 0;
    }

    public function get_cart_count($user_id) {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('cart');

        return $query->num_rows();
    }
}
