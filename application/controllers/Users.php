<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    public function index() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $data['title'] = 'Users Management';
        $data['users'] = $this->User_model->get_users();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }

    public function create() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $data['title'] = 'Create User';

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,customer]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('users/create', $data);
            $this->load->view('templates/footer');
        } else {
            $user_data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role' => $this->input->post('role')
            );

            $this->User_model->create_user($user_data);

            $this->session->set_flashdata('success', 'User created successfully');
            redirect('users');
        }
    }

    public function edit($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $user = $this->User_model->get_user_by_id($id);

        if (empty($user)) {
            show_404();
        }

        $data['title'] = 'Edit User';
        $data['user'] = $user;

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,customer]');

        // Verificar se o email foi alterado
        if ($this->input->post('email') != $user['email']) {
            $this->form_validation->set_rules('email', 'Email', 'is_unique[users.email]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('users/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $user_data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'role' => $this->input->post('role')
            );

            // Atualizar senha apenas se fornecida
            if ($this->input->post('password')) {
                $user_data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
            }

            $this->User_model->update_user($id, $user_data);

            $this->session->set_flashdata('success', 'User updated successfully');
            redirect('users');
        }
    }

    public function delete($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $user = $this->User_model->get_user_by_id($id);

        if (empty($user)) {
            show_404();
        }

        // Não permitir excluir o próprio usuário
        if ($user['id'] == $this->user['id']) {
            $this->session->set_flashdata('error', 'You cannot delete your own account');
            redirect('users');
            return;
        }

        $this->User_model->delete_user($id);

        $this->session->set_flashdata('success', 'User deleted successfully');
        redirect('users');
    }

    public function profile() {
        // Verificar se o usuário está logado
        if (!$this->user) {
            redirect('auth');
        }

        $data['title'] = 'My Profile';
        $data['user'] = $this->user;

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('current_password', 'Current Password', 'callback_verify_password');

        if ($this->input->post('new_password')) {
            $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('users/profile', $data);
            $this->load->view('templates/footer');
        } else {
            $user_data = array(
                'name' => $this->input->post('name')
            );

            // Atualizar senha apenas se fornecida
            if ($this->input->post('new_password')) {
                $user_data['password'] = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
            }

            $this->User_model->update_user($this->user['id'], $user_data);

            // Atualizar dados da sessão
            $updated_user = $this->User_model->get_user_by_id($this->user['id']);
            $this->session->set_userdata('name', $updated_user['name']);

            $this->session->set_flashdata('success', 'Profile updated successfully');
            redirect('users/profile');
        }
    }

    // Callback para verificar senha atual
    public function verify_password($password) {
        if (!password_verify($password, $this->user['password'])) {
            $this->form_validation->set_message('verify_password', 'The {field} is incorrect');
            return FALSE;
        }
        return TRUE;
    }
}
