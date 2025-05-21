<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Coupon_model');
    }

    public function index() {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $coupons = $this->Coupon_model->get_coupons();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($coupons));
    }

    public function view($id) {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (empty($coupon)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Coupon not found']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($coupon));
    }

    public function create() {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['code']) || !isset($data['discount']) || !isset($data['type']) ||
            !isset($data['start_date']) || !isset($data['end_date'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Missing required fields']));
            return;
        }

        // Verificar se o código já existe
        $existing_coupon = $this->Coupon_model->get_coupon_by_code($data['code']);

        if ($existing_coupon) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Coupon code already exists']));
            return;
        }

        $coupon_data = array(
            'code' => strtoupper($data['code']),
            'discount' => $data['discount'],
            'type' => $data['type'],
            'minimum_value' => isset($data['minimum_value']) ? $data['minimum_value'] : 0,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'active' => isset($data['active']) ? $data['active'] : 1
        );

        $coupon_id = $this->Coupon_model->create_coupon($coupon_data);
        $coupon = $this->Coupon_model->get_coupon($coupon_id);

        $this->output
            ->set_status_header(201)
            ->set_content_type('application/json')
            ->set_output(json_encode($coupon));
    }

    public function update($id) {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (empty($coupon)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Coupon not found']));
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $coupon_data = array();

        if (isset($data['code'])) {
            // Verificar se o novo código já existe (exceto para o cupom atual)
            if (strtoupper($data['code']) != $coupon['code']) {
                $existing_coupon = $this->Coupon_model->get_coupon_by_code($data['code']);

                if ($existing_coupon) {
                    $this->output
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Coupon code already exists']));
                    return;
                }
            }

            $coupon_data['code'] = strtoupper($data['code']);
        }

        if (isset($data['discount'])) {
            $coupon_data['discount'] = $data['discount'];
        }

        if (isset($data['type'])) {
            $coupon_data['type'] = $data['type'];
        }

        if (isset($data['minimum_value'])) {
            $coupon_data['minimum_value'] = $data['minimum_value'];
        }

        if (isset($data['start_date'])) {
            $coupon_data['start_date'] = $data['start_date'];
        }

        if (isset($data['end_date'])) {
            $coupon_data['end_date'] = $data['end_date'];
        }

        if (isset($data['active'])) {
            $coupon_data['active'] = $data['active'];
        }

        if (!empty($coupon_data)) {
            $this->Coupon_model->update_coupon($id, $coupon_data);
        }

        $updated_coupon = $this->Coupon_model->get_coupon($id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($updated_coupon));
    }

    public function delete($id) {
        // Verificar se é admin
        if (!$this->user || $this->user['role'] != 'admin') {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (empty($coupon)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Coupon not found']));
            return;
        }

        $this->Coupon_model->delete_coupon($id);

        $this->output
            ->set_status_header(204)
            ->set_content_type('application/json')
            ->set_output(json_encode(null));
    }

    public function validate() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['code']) || !isset($data['subtotal'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Code and subtotal are required']));
            return;
        }

        $coupon = $this->Coupon_model->validate_coupon($data['code'], $data['subtotal']);

        if (!$coupon) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid or expired coupon code']));
            return;
        }

        $discount = $this->Coupon_model->calculate_discount($coupon, $data['subtotal']);

        $response = array(
            'coupon' => $coupon,
            'discount' => $discount
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
