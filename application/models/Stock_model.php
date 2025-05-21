<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_stock($product_id, $variation_id = null) {
        $this->db->where('product_id', $product_id);

        if ($variation_id) {
            $this->db->where('variation_id', $variation_id);
        } else {
            $this->db->where('variation_id IS NULL');
        }

        $query = $this->db->get('stock');
        $result = $query->row_array();

        return $result ? $result['quantity'] : 0;
    }

    public function update_stock($product_id, $variation_id = null, $quantity) {
        $this->db->where('product_id', $product_id);

        if ($variation_id) {
            $this->db->where('variation_id', $variation_id);
        } else {
            $this->db->where('variation_id IS NULL');
        }

        $query = $this->db->get('stock');

        if ($query->num_rows() > 0) {
            // Atualizar registro existente
            $this->db->where('product_id', $product_id);

            if ($variation_id) {
                $this->db->where('variation_id', $variation_id);
            } else {
                $this->db->where('variation_id IS NULL');
            }

            $this->db->update('stock', ['quantity' => $quantity]);
        } else {
            // Inserir novo registro de estoque
            $data = [
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $quantity
            ];

            $this->db->insert('stock', $data);
        }

        return true;
    }

    public function check_stock($product_id, $variation_id = null, $quantity_needed) {
        $available = $this->get_stock($product_id, $variation_id);
        return $available >= $quantity_needed;
    }

    public function decrease_stock($product_id, $variation_id = null, $quantity) {
        $current_stock = $this->get_stock($product_id, $variation_id);
        $new_stock = max(0, $current_stock - $quantity);

        return $this->update_stock($product_id, $variation_id, $new_stock);
    }

    public function increase_stock($product_id, $variation_id = null, $quantity) {
        $current_stock = $this->get_stock($product_id, $variation_id);
        $new_stock = $current_stock + $quantity;

        return $this->update_stock($product_id, $variation_id, $new_stock);
    }

    public function get_low_stock_products($limit = 10, $threshold = 5) {
        $this->db->select('stock.*, products.name as product_name, product_variations.name as variation_name');
        $this->db->from('stock');
        $this->db->join('products', 'products.id = stock.product_id');
        $this->db->join('product_variations', 'product_variations.id = stock.variation_id', 'left');
        $this->db->where('stock.quantity <=', $threshold);
        $this->db->order_by('stock.quantity', 'ASC');
        $this->db->limit($limit);

        $query = $this->db->get();
        return $query->result_array();
    }
}
