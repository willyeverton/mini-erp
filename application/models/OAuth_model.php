<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OAuth_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function generate_tokens($user_id) {
        $access_token = bin2hex(random_bytes(40));
        $refresh_token = bin2hex(random_bytes(40));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $data = array(
            'user_id' => $user_id,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_at' => $expires_at
        );

        $this->db->insert('oauth_tokens', $data);

        return array(
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_at' => $expires_at,
            'token_type' => 'Bearer'
        );
    }

    public function validate_token($token) {
        $query = $this->db->get_where('oauth_tokens', array('access_token' => $token));
        $token_data = $query->row_array();

        if ($token_data && strtotime($token_data['expires_at']) > time()) {
            return $token_data;
        }

        return false;
    }

    public function refresh_token($refresh_token) {
        $query = $this->db->get_where('oauth_tokens', array('refresh_token' => $refresh_token));
        $token_data = $query->row_array();

        if ($token_data) {
            // Invalidar token antigo
            $this->db->delete('oauth_tokens', array('id' => $token_data['id']));

            // Gerar novo token
            return $this->generate_tokens($token_data['user_id']);
        }

        return false;
    }

    public function invalidate_token($token) {
        return $this->db->delete('oauth_tokens', array('access_token' => $token));
    }
}
