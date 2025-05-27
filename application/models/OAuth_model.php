<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OAuth_model extends MY_Model {

    protected $table = 'oauth_tokens';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function generate_token($user_id) {
        // Gerar token aleatório
        $token = bin2hex(random_bytes(32));

        // Definir expiração (1 hora)
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salvar token no banco
        $data = array(
            'user_id' => $user_id,
            'token' => $token,
            'expires' => $expires
        );

        $this->db->insert($this->table, $data);

        return $token;
    }

    public function validate_token($token) {
        $this->db->where('token', $token);
        $this->db->where('expires >', date('Y-m-d H:i:s'));
        $query = $this->db->get($this->table);

        return $query->row_array();
    }

    public function revoke_user_tokens($user_id) {
        return $this->safe_delete($this->table, ['user_id' => $user_id]);
    }

}
