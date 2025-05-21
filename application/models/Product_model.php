<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_products() {
        $this->db->order_by('name', 'ASC');
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
        // Excluir variações
        $this->db->where('product_id', $id);
        $this->db->delete('product_variations');

        // Excluir estoque
        $this->db->where('product_id', $id);
        $this->db->delete('stock');

        // Excluir produto
        $this->db->where('id', $id);
        return $this->db->delete('products');
    }

    public function get_variations($product_id) {
        $this->db->where('product_id', $product_id);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('product_variations');
        return $query->result_array();
    }

    public function get_variation($id) {
        $query = $this->db->get_where('product_variations', array('id' => $id));
        return $query->row_array();
    }

    public function add_variation($product_id, $name) {
        $data = array(
            'product_id' => $product_id,
            'name' => $name
        );

        $this->db->insert('product_variations', $data);
        return $this->db->insert_id();
    }

    public function update_variation($id, $name) {
        $this->db->where('id', $id);
        return $this->db->update('product_variations', array('name' => $name));
    }

    public function delete_variation($id) {
        // Excluir estoque da variação
        $this->db->where('variation_id', $id);
        $this->db->delete('stock');

        // Excluir variação
        $this->db->where('id', $id);
        return $this->db->delete('product_variations');
    }

    public function search_products($keyword) {
        $this->db->like('name', $keyword);
        $this->db->or_like('description', $keyword);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('products');
        return $query->result_array();
    }

    public function get_featured_products($limit = null) {
        $this->db->where('featured', 1);

        if ($limit) {
            $this->db->limit($limit);
        }

        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('products');
        return $query->result_array();
    }

    public function count_products() {
        return $this->db->count_all('products');
    }
}
