<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Coupon_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);

        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }
    }

    public function index() {
        // Configurar paginação
        $this->load->library('pagination');

        $config['base_url'] = site_url('coupons/index');
        $config['total_rows'] = $this->Coupon_model->count_coupons($this->input->get('search'));
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';

        // Estilo da paginação (Bootstrap)
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'Primeiro';
        $config['last_link'] = 'Último';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);

        // Obter página atual
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $offset = ($page - 1) * $config['per_page'];

        // Obter dados dos cupons
        $data['coupons'] = $this->Coupon_model->get_coupons(
            $config['per_page'],
            $offset,
            $this->input->get('search')
        );

        $data['title'] = 'Gerenciamento de Cupons';
        $data['user'] = $this->user;
        $data['pagination'] = $this->pagination->create_links();
        $data['search'] = $this->input->get('search');

        // Registrar o componente de confirmação de exclusão
        load_component('confirmation-modal');

        // Carregar as views
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('coupons/index', $data);
        $this->load->view('templates/footer');
    }

    public function create() {
        $data['title'] = 'Adicionar Novo Cupom';
        $data['user'] = $this->user;

        // Regras de validação
        $this->form_validation->set_rules('code', 'Código', 'required|is_unique[coupons.code]');
        $this->form_validation->set_rules('discount_type', 'Tipo de Desconto', 'required');
        $this->form_validation->set_rules('discount_amount', 'Valor do Desconto', 'required|numeric|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {

            register_js('form', 'coupons');

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('coupons/create', $data);
            $this->load->view('templates/footer');
        } else {
            // Preparar dados para inserção
            $coupon_data = [
                'code' => strtoupper($this->input->post('code')),
                'description' => $this->input->post('description'),
                'type' => $this->input->post('discount_type'),
                'discount' => $this->input->post('discount_amount'),
                'max_discount' => $this->input->post('max_discount') ? $this->input->post('max_discount') : 0,
                'minimum_value' => $this->input->post('min_purchase') ? $this->input->post('min_purchase') : 0,
                'usage_limit' => $this->input->post('usage_limit') ? $this->input->post('usage_limit') : 0,
                'usage_count' => 0,
                'start_date' => $this->input->post('start_date') ? $this->input->post('start_date') : NULL,
                'expires_at' => $this->input->post('end_date') ? $this->input->post('end_date') . ' 23:59:59' : NULL,
                'active' => $this->input->post('active') ? 1 : 0
            ];

            // Salvar cupom
            if ($this->Coupon_model->create_coupon($coupon_data)) {
                $this->session->set_flashdata('success', 'Cupom adicionado com sucesso.');
            } else {
                $this->session->set_flashdata('error', 'Erro ao adicionar cupom.');
            }

            redirect('coupons');
        }
    }

    public function edit($id = NULL) {
        if (!$id) {
            show_404();
        }

        // Obter dados do cupom
        $coupon_data = $this->Coupon_model->get_coupon_by_id($id);

        if (!$coupon_data) {
            show_404();
        }

        $data['title'] = 'Editar Cupom';
        $data['user'] = $this->user;
        $data['coupon'] = $coupon_data;

        // Regras de validação
        $this->form_validation->set_rules('code', 'Código', 'required');
        $this->form_validation->set_rules('discount_type', 'Tipo de Desconto', 'required');
        $this->form_validation->set_rules('discount_amount', 'Valor do Desconto', 'required|numeric|greater_than[0]');

        // Verificar se o código foi alterado
        if ($this->input->post('code') != $coupon_data['code']) {
            $this->form_validation->set_rules('code', 'Código', 'required|is_unique[coupons.code]');
        }

        if ($this->form_validation->run() === FALSE) {
            register_js('form', 'coupons');

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('coupons/edit', $data);
            $this->load->view('templates/footer');
        } else {
            // Preparar dados para atualização
            $update_data = [
                'code' => strtoupper($this->input->post('code')),
                'description' => $this->input->post('description'),
                'type' => $this->input->post('discount_type'), // Campo no DB é 'type'
                'discount' => $this->input->post('discount_amount'), // Campo no DB é 'discount'
                'max_discount' => $this->input->post('max_discount') ? $this->input->post('max_discount') : 0,
                'minimum_value' => $this->input->post('min_purchase') ? $this->input->post('min_purchase') : 0, // Campo no DB é 'minimum_value'
                'usage_limit' => $this->input->post('usage_limit') ? $this->input->post('usage_limit') : 0,
                'usage_count' => isset($coupon_data['usage_count']) ? $coupon_data['usage_count'] : 0,
                'start_date' => $this->input->post('start_date') ? $this->input->post('start_date') : NULL,
                'expires_at' => $this->input->post('end_date') ? $this->input->post('end_date') . ' 23:59:59' : NULL, // Campo no DB é 'expires_at'
                'active' => $this->input->post('active') ? 1 : 0
            ];

            // Atualizar cupom
            if ($this->Coupon_model->update_coupon($id, $update_data)) {
                $this->session->set_flashdata('success', 'Cupom atualizado com sucesso.');
            } else {
                $this->session->set_flashdata('error', 'Erro ao atualizar cupom.');
            }

            redirect('coupons');
        }
    }

    public function delete($id = NULL) {
        if (!$id) {
            show_404();
        }

        // Obter dados do cupom
        $coupon_data = $this->Coupon_model->get_coupon_by_id($id);

        if (!$coupon_data) {
            show_404();
        }

        // Excluir cupom usando o método safe_delete
        $result = $this->Coupon_model->delete_coupon($id);

        // Verificar o resultado da operação
        if (isset($result['success']) && $result['success']) {
            $this->session->set_flashdata('success', $result['message'] ?? 'Cupom excluído com sucesso.');
        } else {
            $this->session->set_flashdata('error', $result['message'] ?? 'Erro ao excluir cupom.');
        }

        redirect('coupons');
    }

    public function view($id = NULL) {
        if (!$id) {
            show_404();
        }

        // Obter dados do cupom
        $coupon_data = $this->Coupon_model->get_coupon_by_id($id);

        if (!$coupon_data) {
            show_404();
        }

        $data['title'] = 'Detalhes do Cupom';
        $data['user'] = $this->user;
        $data['coupon'] = $coupon_data;
        // Registrar o componente de confirmação de exclusão
        load_component('confirmation-modal');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('coupons/view', $data);
        $this->load->view('templates/footer');
    }

    public function toggle_status($id = NULL) {
        if (!$id) {
            show_404();
        }

        // Obter dados do cupom
        $coupon = $this->Coupon_model->get_coupon_by_id($id);

        if (!$coupon) {
            show_404();
        }

        // Alternar status ativo/inativo
        $new_status = $coupon['active'] ? 0 : 1;

        if ($this->Coupon_model->update_coupon($id, ['active' => $new_status])) {
            $this->session->set_flashdata('success', 'Status do cupom alterado com sucesso.');
        } else {
            $this->session->set_flashdata('error', 'Erro ao alterar status do cupom.');
        }

        redirect('coupons');
    }
}
