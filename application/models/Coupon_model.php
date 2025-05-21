<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_coupons() {
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('coupons');
        return $query->result_array();
    }

    public function get_coupon($id) {
        $query = $this->db->get_where('coupons', array('id' => $id));
        return $query->row_array();
    }

    public function get_coupon_by_code($code) {
        $query = $this->db->get_where('coupons', array('code' => strtoupper($code), 'active' => 1));
        return $query->row_array();
    }

    public function create_coupon($data) {
        $this->db->insert('coupons', $data);
        return $this->db->insert_id();
    }

    public function update_coupon($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('coupons', $data);
    }

    public function delete_coupon($id) {
        return $this->db->delete('coupons', array('id' => $id));
    }

    public function validate_coupon($code, $subtotal) {
        $today = date('Y-m-d');

        $this->db->where('code', strtoupper($code));
        $this->db->where('active', 1);
        $this->db->where('start_date <=', $today);
        $this->db->where('end_date >=', $today);

        $query = $this->db->get('coupons');
        $coupon = $query->row_array();

        if (!$coupon) {
            return false;
        }

        // Verificar valor mínimo
        if ($coupon['minimum_value'] > 0 && $subtotal < $coupon['minimum_value']) {
            return false;
        }

        return $coupon;
    }

    public function calculate_discount($coupon, $subtotal) {
        if ($coupon['type'] == 'percentage') {
            $discount = ($subtotal * $coupon['discount']) / 100;
        } else {
            $discount = $coupon['discount'];

            // Garantir que o desconto não seja maior que o subtotal
            if ($discount > $subtotal) {
                $discount = $subtotal;
            }
        }

        return $discount;
    }
}
