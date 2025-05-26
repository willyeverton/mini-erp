<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('OAuth_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->library('session');
    }

    public function index() {
        // Se já estiver logado, redirecionar para o dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
            return;
        }

        $data['title'] = 'Login';

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/footer');
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $user = $this->User_model->login($email, $password);

            if ($user) {
                // Criar sessão
                $user_data = array(
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'logged_in' => TRUE
                );

                $this->session->set_userdata($user_data);

                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Invalid email or password');
                redirect('auth');
            }
        }
    }

    public function register() {
        // Se já estiver logado, redirecionar para o dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $data['title'] = 'Register';

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('auth/register');
            $this->load->view('templates/footer');
        } else {
            $user_data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role' => 'customer' // Papel padrão para novos usuários
            );

            $user_id = $this->User_model->create_user($user_data);

            if ($user_id) {
                $this->session->set_flashdata('success', 'Registration successful. You can now login.');
                redirect('auth');
            } else {
                $this->session->set_flashdata('error', 'Registration failed. Please try again.');
                redirect('auth/register');
            }
        }
    }

    public function logout() {
        // Destruir sessão
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('name');
        $this->session->unset_userdata('role');
        $this->session->unset_userdata('logged_in');

        $this->session->set_flashdata('success', 'You have been logged out');
        redirect('auth');
    }

    public function token() {
        // Endpoint para obter token de acesso (API)
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['email']) || !isset($data['password'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Email and password are required']));
            return;
        }

        $user = $this->User_model->login($data['email'], $data['password']);

        if (!$user) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid credentials']));
            return;
        }

        // Gerar token de acesso
        $token = $this->OAuth_model->generate_token($user['id']);

        $response = array(
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hora
            'user' => array(
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            )
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function reset_password() {
        $data['title'] = 'Reset Password';

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('auth/reset_password');
            $this->load->view('templates/footer');
        } else {
            $email = $this->input->post('email');
            $user = $this->User_model->get_user_by_email($email);

            if ($user) {
                // Gerar token de redefinição
                $token = bin2hex(random_bytes(32));
                $this->User_model->set_reset_token($user['id'], $token);

                // Enviar email com link de redefinição
                $this->load->library('email');

                $config['mailtype'] = 'html';
                $this->email->initialize($config);

                $this->email->from('noreply@minierp.com', 'Mini ERP');
                $this->email->to($email);

                $this->email->subject('Password Reset Request');

                $message = '<p>You have requested to reset your password.</p>';
                $message .= '<p>Please click the link below to reset your password:</p>';
                $message .= '<p><a href="' . base_url('auth/new_password/' . $token) . '">Reset Password</a></p>';
                $message .= '<p>If you did not request this, please ignore this email.</p>';

                $this->email->message($message);

                $this->email->send();
            }

            // Sempre mostrar a mesma mensagem para evitar enumeração de usuários
            $this->session->set_flashdata('success', 'If your email exists in our system, you will receive a password reset link.');
            redirect('auth');
        }
    }

    public function new_password($token = null) {
        if (!$token) {
            show_404();
        }

        $user = $this->User_model->get_user_by_reset_token($token);

        if (!$user) {
            $this->session->set_flashdata('error', 'Invalid or expired reset token');
            redirect('auth');
        }

        $data['title'] = 'New Password';
        $data['token'] = $token;

        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('auth/new_password', $data);
            $this->load->view('templates/footer');
        } else {
            $password = $this->input->post('password');

            $this->User_model->update_password($user['id'], $password);
            $this->User_model->clear_reset_token($user['id']);

            $this->session->set_flashdata('success', 'Your password has been updated. You can now login with your new password.');
            redirect('auth');
        }
    }
}
