<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('OAuth_model');
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->library('form_validation');
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function login() {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/login');
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $user = $this->User_model->get_user_by_email($email);

            if ($user && password_verify($password, $user['password'])) {
                // Gerar token OAuth2
                $token_data = $this->OAuth_model->generate_tokens($user['id']);

                $user_data = array(
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'access_token' => $token_data['access_token'],
                    'logged_in' => TRUE
                );

                $this->session->set_userdata($user_data);
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Invalid email or password');
                $this->load->view('auth/login');
            }
        }
    }

    public function register() {
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/register');
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role' => 'user'
            );

            $user_id = $this->User_model->create_user($data);

            if ($user_id) {
                $this->session->set_flashdata('success', 'Registration successful! Please login to continue.');
                redirect('auth');
            } else {
                $this->session->set_flashdata('error', 'Error registering user');
                $this->load->view('auth/register');
            }
        }
    }

    public function logout() {
        // Invalidar token
        if ($this->session->userdata('user_id') && $this->session->userdata('access_token')) {
            $this->OAuth_model->invalidate_token($this->session->userdata('access_token'));
        }

        $this->session->sess_destroy();
        redirect('auth');
    }

    public function token() {
        // Endpoint para obter token via API
        $grant_type = $this->input->post('grant_type');

        if ($grant_type === 'password') {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $user = $this->User_model->get_user_by_email($email);

            if ($user && password_verify($password, $user['password'])) {
                $token_data = $this->OAuth_model->generate_tokens($user['id']);
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($token_data));
            } else {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Invalid credentials']));
            }
        } elseif ($grant_type === 'refresh_token') {
            $refresh_token = $this->input->post('refresh_token');
            $token_data = $this->OAuth_model->refresh_token($refresh_token);

            if ($token_data) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($token_data));
            } else {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Invalid refresh token']));
            }
        } else {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid grant type']));
        }
    }
}
