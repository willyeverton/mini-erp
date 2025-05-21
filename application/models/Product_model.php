<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_products() {
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('products');
        return $query->result_array();
    }

    public function get_product($id) {
        $query = $this->db->get_where('products', array('id' => $id));
        return $query->row_array();
    }

    public function create_product($data) {
        $this->db->insert('products', $data);
        return $this->db->insert_id();
    }

    public function update_product($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('products', $data);
    }

    public function delete_product($id) {
        // Primeiro excluir as variações e estoque relacionados
        $this->db->delete('variations', array('product_id' => $id));
        $this->db->delete('stock', array('product_id' => $id));

        // Depois excluir o produto
        return $this->db->delete('products', array('id' => $id));
    }

    public function add_variation($product_id, $name) {
        $data = array(
            'product_id' => $product_id,
            'name' => $name
        );

        $this->db->insert('variations', $data);
        return $this->db->insert_id();
    }

    public function update_variation($variation_id, $name) {
        $data = array('name' => $name);
        $this->db->where('id', $variation_id);
        return $this->db->update('variations', $data);
    }

    public function delete_variation($variation_id) {
        // Excluir estoque relacionado
        $this->db->delete('stock', array('variation_id' => $variation_id));

        // Excluir variação
        return $this->db->delete('variations', array('id' => $variation_id));
    }

    public function get_variations($product_id) {
        $this->db->where('product_id', $product_id);
        $query = $this->db->get('variations');
        return $query->result_array();
    }
}
