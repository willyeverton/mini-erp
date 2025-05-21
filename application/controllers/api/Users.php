<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Users extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');

        // Verificar autenticação para todos os métodos exceto register
        if ($this->router->method !== 'register_post') {
            $this->verify_token();
        }
    }

    public function index_get() {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to view all users'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $users = $this->User_model->get_users();

        // Remover senhas dos resultados
        foreach ($users as &$user) {
            unset($user['password']);
            unset($user['reset_token']);
            unset($user['reset_token_expires']);
        }

        $this->response($users, REST_Controller::HTTP_OK);
    }

    public function view_get($id = null) {
        // Se nenhum ID for fornecido, retornar o usuário atual
        if ($id === null) {
            $user = $this->user;
        } else {
            // Verificar permissões de administrador para visualizar outros usuários
            if ($this->user['role'] != 'admin' && $id != $this->user['id']) {
                $this->response(['error' => 'You do not have permission to view this user'], REST_Controller::HTTP_FORBIDDEN);
                return;
            }

            $user = $this->User_model->get_user_by_id($id);
        }

        if (!$user) {
            $this->response(['error' => 'User not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Remover dados sensíveis
        unset($user['password']);
        unset($user['reset_token']);
        unset($user['reset_token_expires']);

        $this->response($user, REST_Controller::HTTP_OK);
    }

    public function register_post() {
        $data = $this->post();

        // Validação básica
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
            $this->response(['error' => 'Name, email and password are required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Verificar se o email já existe
        $existing_user = $this->User_model->get_user_by_email($data['email']);

        if ($existing_user) {
            $this->response(['error' => 'Email already exists'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->response(['error' => 'Invalid email format'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Validar tamanho da senha
        if (strlen($data['password']) < 6) {
            $this->response(['error' => 'Password must be at least 6 characters'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $user_data = array(
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'customer' // Papel padrão para novos usuários
        );

        $user_id = $this->User_model->create_user($user_data);

        // Gerar token de acesso para o novo usuário
        $this->load->model('OAuth_model');
        $token = $this->OAuth_model->generate_token($user_id);

        $user = $this->User_model->get_user_by_id($user_id);

        // Remover dados sensíveis
        unset($user['password']);
        unset($user['reset_token']);
        unset($user['reset_token_expires']);

        $response = array(
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hora
            'user' => $user
        );

        $this->response($response, REST_Controller::HTTP_CREATED);
    }

    public function update_put() {
        $data = $this->put();

        $user_data = array();

        if (isset($data['name'])) {
            $user_data['name'] = $data['name'];
        }

        if (isset($data['email']) && $data['email'] != $this->user['email']) {
            // Verificar se o email já existe
            $existing_user = $this->User_model->get_user_by_email($data['email']);

            if ($existing_user) {
                $this->response(['error' => 'Email already exists'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            // Validar formato de email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->response(['error' => 'Invalid email format'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $user_data['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            // Validar tamanho da senha
            if (strlen($data['password']) < 6) {
                $this->response(['error' => 'Password must be at least 6 characters'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $user_data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!empty($user_data)) {
            $this->User_model->update_user($this->user['id'], $user_data);
        }

        $updated_user = $this->User_model->get_user_by_id($this->user['id']);

        // Remover dados sensíveis
        unset($updated_user['password']);
        unset($updated_user['reset_token']);
        unset($updated_user['reset_token_expires']);

        $this->response($updated_user, REST_Controller::HTTP_OK);
    }

    public function update_user_put($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to update other users'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $user = $this->User_model->get_user_by_id($id);

        if (!$user) {
            $this->response(['error' => 'User not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $data = $this->put();

        $user_data = array();

        if (isset($data['name'])) {
            $user_data['name'] = $data['name'];
        }

        if (isset($data['email']) && $data['email'] != $user['email']) {
            // Verificar se o email já existe
            $existing_user = $this->User_model->get_user_by_email($data['email']);

            if ($existing_user && $existing_user['id'] != $id) {
                $this->response(['error' => 'Email already exists'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            // Validar formato de email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->response(['error' => 'Invalid email format'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $user_data['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            // Validar tamanho da senha
            if (strlen($data['password']) < 6) {
                $this->response(['error' => 'Password must be at least 6 characters'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $user_data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (isset($data['role'])) {
            // Validar papel
            if (!in_array($data['role'], array('admin', 'customer'))) {
                $this->response(['error' => 'Invalid role'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $user_data['role'] = $data['role'];
        }

        if (!empty($user_data)) {
            $this->User_model->update_user($id, $user_data);
        }

        $updated_user = $this->User_model->get_user_by_id($id);

        // Remover dados sensíveis
        unset($updated_user['password']);
        unset($updated_user['reset_token']);
        unset($updated_user['reset_token_expires']);

        $this->response($updated_user, REST_Controller::HTTP_OK);
    }

    public function delete_delete($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to delete users'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        // Não permitir que o usuário exclua a si mesmo
        if ($id == $this->user['id']) {
            $this->response(['error' => 'You cannot delete your own account'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $user = $this->User_model->get_user_by_id($id);

        if (!$user) {
            $this->response(['error' => 'User not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $this->User_model->delete_user($id);

        $this->response(['success' => 'User deleted successfully'], REST_Controller::HTTP_OK);
    }

    private function verify_token() {
        $headers = $this->input->request_headers();

        if (!isset($headers['Authorization'])) {
            $this->response(['error' => 'Authorization header not found'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        $token_parts = explode(' ', $headers['Authorization']);

        if (count($token_parts) != 2 || $token_parts[0] != 'Bearer') {
            $this->response(['error' => 'Invalid authorization format'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        $this->load->model('OAuth_model');
        $token_data = $this->OAuth_model->validate_token($token_parts[1]);

        if (!$token_data) {
            $this->response(['error' => 'Invalid or expired token'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        $this->user = $this->User_model->get_user_by_id($token_data['user_id']);

        if (!$this->user) {
            $this->response(['error' => 'User not found'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }
}
