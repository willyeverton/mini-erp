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

        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return $result['quantity'];
        }

        return 0;
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
            // Atualizar estoque existente
            $this->db->where('product_id', $product_id);

            if ($variation_id) {
                $this->db->where('variation_id', $variation_id);
            } else {
                $this->db->where('variation_id IS NULL');
            }

            return $this->db->update('stock', array('quantity' => $quantity));
        } else {
            // Criar novo registro de estoque
            $data = array(
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $quantity
            );

            return $this->db->insert('stock', $data);
        }
    }

    public function increase_stock($product_id, $variation_id = null, $quantity) {
        $current_stock = $this->get_stock($product_id, $variation_id);
        $new_stock = $current_stock + $quantity;

        return $this->update_stock($product_id, $variation_id, $new_stock);
    }

    public function decrease_stock($product_id, $variation_id = null, $quantity) {
        $current_stock = $this->get_stock($product_id, $variation_id);
        $new_stock = $current_stock - $quantity;

        if ($new_stock < 0) {
            $new_stock = 0;
        }

        return $this->update_stock($product_id, $variation_id, $new_stock);
    }

    public function check_stock($product_id, $variation_id = null, $quantity) {
        $current_stock = $this->get_stock($product_id, $variation_id);
        return $current_stock >= $quantity;
    }

    public function get_low_stock_products($limit = 10) {
        $this->db->select('stock.*, products.name as product_name, product_variations.name as variation_name');
        $this->db->from('stock');
        $this->db->join('products', 'products.id = stock.product_id');
        $this->db->join('product_variations', 'product_variations.id = stock.variation_id', 'left');
        $this->db->where('stock.quantity <=', 5);
        $this->db->order_by('stock.quantity', 'ASC');
        $this->db->limit($limit);

        $query = $this->db->get();
        return $query->result_array();
    }
}
