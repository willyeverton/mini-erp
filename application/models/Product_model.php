<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends MY_Model {

    protected $table = 'products';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_products($limit = NULL, $offset = NULL, $search = NULL) {
        if ($search) {
            $this->db->like('name', $search);
            $this->db->or_like('description', $search);
        }

        $this->db->order_by('name', 'ASC');

        if ($limit !== NULL) {
            return $this->db->get($this->table, $limit, $offset)->result_array();
        }

        return $this->db->get($this->table)->result_array();
    }

    public function get_product($id) {
        $query = $this->db->get_where($this->table, array('id' => $id));
        return $query->row_array();
    }

    public function create_product($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update_product($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete_product($id) {
        // Excluir variações
        $this->db->where('product_id', $id);
        $this->db->delete('product_variations');

        // Excluir estoque
        $this->db->where('product_id', $id);
        $this->db->delete('stock');

        // Excluir produto
        return $this->safe_delete($this->table, ['id' => $id]);
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
        $this->safe_delete('stock', ['variation_id' => $id]);

        // Excluir variação
        $this->safe_delete('product_variations', ['id' => $id]);
    }

    public function search_products($keyword) {
        $this->db->like('name', $keyword);
        $this->db->or_like('description', $keyword);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    public function get_featured_products($limit = null) {
        $this->db->where('featured', 1);

        if ($limit) {
            $this->db->limit($limit);
        }

        $this->db->order_by('name', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    public function count_products($search = NULL) {
        if ($search) {
            $this->db->like('name', $search);
            $this->db->or_like('description', $search);
        }

        return $this->db->count_all_results($this->table);
    }
}
