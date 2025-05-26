<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Coupons extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Coupon_model');

        // Verificar autenticação para métodos que requerem
        if ($this->router->method !== 'validate_post') {
            $this->verify_token();
        }
    }

    public function index_get() {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to view all coupons'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $coupons = $this->Coupon_model->get_coupons();
        $this->response($coupons, REST_Controller::HTTP_OK);
    }

    public function view_get($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to view coupon details'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (!$coupon) {
            $this->response(['error' => 'Coupon not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $this->response($coupon, REST_Controller::HTTP_OK);
    }

    public function create_post() {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to create coupons'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $data = $this->post();

        // Validação básica
        if (!isset($data['code']) || !isset($data['type']) || !isset($data['discount'])) {
            $this->response(['error' => 'Code, type and discount are required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Verificar se o código já existe
        $existing_coupon = $this->Coupon_model->get_coupon_by_code($data['code']);

        if ($existing_coupon) {
            $this->response(['error' => 'Coupon code already exists'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $coupon_data = array(
            'code' => strtoupper($data['code']),
            'type' => $data['type'],
            'discount' => $data['discount'],
            'minimum_value' => isset($data['minimum_value']) ? $data['minimum_value'] : 0
        );

        // Adicionar data de expiração, se fornecida
        if (isset($data['expires_at']) && !empty($data['expires_at'])) {
            $coupon_data['expires_at'] = $data['expires_at'] . ' 23:59:59';
        }

        $coupon_id = $this->Coupon_model->create_coupon($coupon_data);

        $coupon = $this->Coupon_model->get_coupon($coupon_id);
        $this->response($coupon, REST_Controller::HTTP_CREATED);
    }

    public function update_put($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to update coupons'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (!$coupon) {
            $this->response(['error' => 'Coupon not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $data = $this->put();

        $coupon_data = array();

        if (isset($data['code'])) {
            // Verificar se o código já existe (exceto para o cupom atual)
            $existing_coupon = $this->Coupon_model->get_coupon_by_code($data['code']);

            if ($existing_coupon && $existing_coupon['id'] != $id) {
                $this->response(['error' => 'Coupon code already exists'], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $coupon_data['code'] = strtoupper($data['code']);
        }

        if (isset($data['type'])) {
            $coupon_data['type'] = $data['type'];
        }

        if (isset($data['discount'])) {
            $coupon_data['discount'] = $data['discount'];
        }

        if (isset($data['minimum_value'])) {
            $coupon_data['minimum_value'] = $data['minimum_value'];
        }

        if (isset($data['expires_at'])) {
            if (empty($data['expires_at'])) {
                $coupon_data['expires_at'] = null;
            } else {
                $coupon_data['expires_at'] = $data['expires_at'] . ' 23:59:59';
            }
        }

        if (!empty($coupon_data)) {
            $this->Coupon_model->update_coupon($id, $coupon_data);
        }

        $updated_coupon = $this->Coupon_model->get_coupon($id);
        $this->response($updated_coupon, REST_Controller::HTTP_OK);
    }

    public function delete_delete($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to delete coupons'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (!$coupon) {
            $this->response(['error' => 'Coupon not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $this->Coupon_model->delete_coupon($id);

        $this->response(['success' => 'Coupon deleted successfully'], REST_Controller::HTTP_OK);
    }

    public function validate_post() {
        $data = $this->post();

        // Validação básica
        if (!isset($data['code']) || !isset($data['subtotal'])) {
            $this->response(['error' => 'Code and subtotal are required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $code = $data['code'];
        $subtotal = $data['subtotal'];

        $coupon = $this->Coupon_model->validate_coupon($code, $subtotal);

        if (!$coupon) {
            $this->response(['error' => 'Invalid or expired coupon'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $discount = $this->Coupon_model->calculate_discount($coupon, $subtotal);

        $response = array(
            'coupon' => $coupon,
            'discount' => $discount
        );

        $this->response($response, REST_Controller::HTTP_OK);
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

        $this->load->model('User_model');
        $this->user = $this->User_model->get_user_by_id($token_data['user_id']);

        if (!$this->user) {
            $this->response(['error' => 'User not found'], REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }
}
