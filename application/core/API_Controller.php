<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class API_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // Desabilitar verificação CSRF para endpoints de API
        $this->output->set_content_type('application/json');

        // Método crítico para desabilitar CSRF para este controller
        $this->security->csrf_verify = FALSE;
    }

    /**
     * Responder com JSON
     *
     * @param mixed $data Dados a serem retornados
     * @param int $status_code Código de status HTTP
     */
    protected function json_response($data, $status_code = 200) {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * Obter dados JSON do corpo da requisição
     *
     * @return array
     */
    protected function get_json_input() {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?: [];
    }
}
