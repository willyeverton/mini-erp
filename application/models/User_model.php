<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_users() {
        $query = $this->db->get('users');
        return $query->result_array();
    }

    public function get_user_by_id($id) {
        $query = $this->db->get_where('users', array('id' => $id));
        return $query->row_array();
    }

    public function get_user_by_email($email) {
        $query = $this->db->get_where('users', array('email' => $email));
        return $query->row_array();
    }

    public function create_user($data) {
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function update_user($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    public function delete_user($id) {
        $this->db->where('id', $id);
        return $this->db->delete('users');
    }

    public function login($email, $password) {
        $user = $this->get_user_by_email($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function set_reset_token($user_id, $token) {
        $data = array(
            'reset_token' => $token,
            'reset_token_expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        );

        $this->db->where('id', $user_id);
        return $this->db->update('users', $data);
    }

    public function get_user_by_reset_token($token) {
        $this->db->where('reset_token', $token);
        $this->db->where('reset_token_expires >', date('Y-m-d H:i:s'));
        $query = $this->db->get('users');
        return $query->row_array();
    }

    public function update_password($user_id, $password) {
        $data = array(
            'password' => password_hash($password, PASSWORD_DEFAULT)
        );

        $this->db->where('id', $user_id);
        return $this->db->update('users', $data);
    }

    public function clear_reset_token($user_id) {
        $data = array(
            'reset_token' => null,
            'reset_token_expires' => null
        );

        $this->db->where('id', $user_id);
        return $this->db->update('users', $data);
    }

    public function count_users_by_role($role) {
        $this->db->where('role', $role);
        return $this->db->count_all_results('users');
    }

    public function get_customer_acquisition($start_date, $end_date) {
        // Obter todos os dias no intervalo
        $days = [];
        $current = strtotime($start_date);
        $end = strtotime($end_date);

        while ($current <= $end) {
            $days[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        $result = [];

        foreach ($days as $day) {
            // Contar novos usuários neste dia
            $this->db->where('DATE(created_at)', $day);
            $this->db->where('role', 'customer');
            $new_customers = $this->db->count_all_results('users');

            // Contar total de usuários até este dia
            $this->db->where('DATE(created_at) <=', $day);
            $this->db->where('role', 'customer');
            $total_customers = $this->db->count_all_results('users');

            $result[] = [
                'date' => $day,
                'new_customers' => $new_customers,
                'total_customers' => $total_customers
            ];
        }

        return $result;
    }
}
