<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);

        // Verificar permissões de administrador
        if (!$this->user) {
            redirect('auth');
        }
    }

    public function index() {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            redirect('auth');
            return;
        }

        // Configurar paginação
        $this->load->library('pagination');

        $config['base_url'] = site_url('users/index');
        $config['total_rows'] = $this->User_model->count_users($this->input->get('search'));
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

        // Obter dados dos usuários
        $data['users'] = $this->User_model->get_users(
            $config['per_page'],
            $offset,
            $this->input->get('search')
        );

        $data['title'] = 'Gerenciamento de Usuários';
        $data['user'] = $this->user;
        $data['pagination'] = $this->pagination->create_links();
        $data['search'] = $this->input->get('search');

        // Registrar o componente de confirmação de exclusão
        load_component('confirmation-modal');

        // Carregar as views
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }

    public function create() {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            redirect('auth');
            return;
        }

        $data['title'] = 'Adicionar Novo Usuário';
        $data['user'] = $this->user;

        // Regras de validação
        $this->form_validation->set_rules('name', 'Nome', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Senha', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirmar Senha', 'required|matches[password]');
        $this->form_validation->set_rules('role', 'Função', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('users/create', $data);
            $this->load->view('templates/footer');
        } else {
            // Preparar dados para inserção
            $user_data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'password' => $this->input->post('password'),
                'role' => $this->input->post('role'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Salvar usuário
            if ($this->User_model->create_user($user_data)) {
                $this->session->set_flashdata('success', 'Usuário adicionado com sucesso.');
            } else {
                $this->session->set_flashdata('error', 'Erro ao adicionar usuário.');
            }

            redirect('users');
        }
    }

    public function edit($id = NULL) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin' && $this->user['id'] != $id) {
            redirect('auth');
            return;
        }
        if (!$id) {
            show_404();
        }

        // Obter dados do usuário
        $user_data = $this->User_model->get_user_by_id($id);

        if (!$user_data) {
            show_404();
        }

        $data['title'] = 'Editar Usuário';
        $data['user'] = $this->user;
        $data['user_data'] = $user_data;

        // Regras de validação
        $this->form_validation->set_rules('name', 'Nome', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        // Verificar se o email foi alterado
        if ($this->input->post('email') != $user_data['email']) {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        }

        // Senha é opcional na edição
        if ($this->input->post('password')) {
            $this->form_validation->set_rules('password', 'Senha', 'min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirmar Senha', 'matches[password]');
        }

        $this->form_validation->set_rules('role', 'Função', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('users/edit', $data);
            $this->load->view('templates/footer');
        } else {
            // Preparar dados para atualização
            $update_data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'role' => $this->input->post('role')
            ];

            // Incluir senha apenas se fornecida
            if ($this->input->post('password')) {
                $update_data['password'] = $this->input->post('password');
            }

            // Atualizar usuário
            if ($this->User_model->update_user($id, $update_data)) {
                $this->session->set_flashdata('success', 'Usuário atualizado com sucesso.');
            } else {
                $this->session->set_flashdata('error', 'Erro ao atualizar usuário.');
            }

            redirect('users');
        }
    }

    public function delete($id = NULL) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            redirect('auth');
            return;
        }

        if (!$id) {
            show_404();
        }

        // Obter dados do usuário
        $user_data = $this->User_model->get_user_by_id($id);

        if (!$user_data) {
            show_404();
        }

        // Verificar se não está excluindo o próprio usuário logado
        if ($user_data['id'] == $this->user['id']) {
            $this->session->set_flashdata('error', 'Você não pode excluir seu próprio usuário.');
            redirect('users');
        }

        // Excluir usuário usando o método safe_delete
        $result = $this->User_model->delete_user($id);

        // Verificar o resultado da operação
        if (isset($result['success']) && $result['success']) {
            $this->session->set_flashdata('success', $result['message'] ?? 'Usuário excluído com sucesso.');
        } else {
            $this->session->set_flashdata('error', $result['message'] ?? 'Erro ao excluir usuário.');
        }

        redirect('users');
    }

    public function view($id = NULL) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin' && $this->user['id'] != $id) {
            redirect('auth');
            return;
        }

        if (!$id) {
            show_404();
        }

        // Obter dados do usuário
        $user_data = $this->User_model->get_user_by_id($id);

        if (!$user_data) {
            show_404();
        }

        $data['title'] = 'Detalhes do Usuário';
        $data['user'] = $this->user;
        $data['user_data'] = $user_data;

        // Registrar o componente de confirmação de exclusão
        load_component('confirmation-modal');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('users/view', $data);
        $this->load->view('templates/footer');
    }
}
