<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $user = null;

    public function __construct() {
        parent::__construct();
        $this->load->model('OAuth_model');
        $this->load->model('User_model');
        $this->load->library('session');

        // Verificar autenticação
        $this->_check_auth();
    }

    private function _check_auth() {
        // Verificar se está autenticado via sessão
        if ($this->session->userdata('logged_in')) {
            $this->user = $this->User_model->get_user_by_id($this->session->userdata('user_id'));
            return;
        }

        // Verificar se está autenticado via token (API)
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {
            $token_parts = explode(' ', $headers['Authorization']);
            if (count($token_parts) == 2 && $token_parts[0] == 'Bearer') {
                $token = $token_parts[1];
                $token_data = $this->OAuth_model->validate_token($token);

                if ($token_data) {
                    $this->user = $this->User_model->get_user_by_id($token_data['user_id']);
                    return;
                }
            }
        }

        // Se não estiver autenticado e a rota exigir autenticação
        if ($this->router->class != 'auth' && $this->router->method != 'token') {
            if ($this->input->is_ajax_request() || strpos($this->router->class, 'api') === 0) {
                // Resposta para API
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Unauthorized']))
                    ->_display();
                exit;
            } else {
                // Redirecionamento para página de login
                redirect('auth');
            }
        }
    }
}
