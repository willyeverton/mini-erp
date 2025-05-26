<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends MY_Controller {

    public function __construct() {
        parent::__construct();

        // Verificar se é um cliente
        if (!$this->user || $this->user['role'] != 'customer') {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'Área do Cliente';
        $data['user'] = $this->user;

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('customer/index', $data);
        $this->load->view('templates/footer');
    }
}
