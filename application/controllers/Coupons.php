<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Coupon_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    public function index() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $data['title'] = 'Coupons Management';
        $data['coupons'] = $this->Coupon_model->get_coupons();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('coupons/index', $data);
        $this->load->view('templates/footer');
    }

    public function create() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $data['title'] = 'Create Coupon';

        $this->form_validation->set_rules('code', 'Code', 'required|is_unique[coupons.code]');
        $this->form_validation->set_rules('type', 'Type', 'required|in_list[percentage,fixed]');
        $this->form_validation->set_rules('discount', 'Discount', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('minimum_value', 'Minimum Value', 'numeric|greater_than_equal_to[0]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('coupons/create', $data);
            $this->load->view('templates/footer');
        } else {
            $coupon_data = array(
                'code' => strtoupper($this->input->post('code')),
                'type' => $this->input->post('type'),
                'discount' => $this->input->post('discount'),
                'minimum_value' => $this->input->post('minimum_value') ? $this->input->post('minimum_value') : 0
            );

            // Adicionar data de expiração, se fornecida
            if ($this->input->post('expires_at')) {
                $coupon_data['expires_at'] = $this->input->post('expires_at') . ' 23:59:59';
            }

            $this->Coupon_model->create_coupon($coupon_data);

            $this->session->set_flashdata('success', 'Coupon created successfully');
            redirect('coupons');
        }
    }

    public function edit($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (empty($coupon)) {
            show_404();
        }

        $data['title'] = 'Edit Coupon';
        $data['coupon'] = $coupon;

        $this->form_validation->set_rules('code', 'Code', 'required');
        $this->form_validation->set_rules('type', 'Type', 'required|in_list[percentage,fixed]');
        $this->form_validation->set_rules('discount', 'Discount', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('minimum_value', 'Minimum Value', 'numeric|greater_than_equal_to[0]');

        // Verificar se o código foi alterado
        if ($this->input->post('code') != $coupon['code']) {
            $this->form_validation->set_rules('code', 'Code', 'is_unique[coupons.code]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar');
            $this->load->view('coupons/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $coupon_data = array(
                'code' => strtoupper($this->input->post('code')),
                'type' => $this->input->post('type'),
                'discount' => $this->input->post('discount'),
                'minimum_value' => $this->input->post('minimum_value') ? $this->input->post('minimum_value') : 0
            );

            // Adicionar data de expiração, se fornecida
            if ($this->input->post('expires_at')) {
                $coupon_data['expires_at'] = $this->input->post('expires_at') . ' 23:59:59';
            } else {
                $coupon_data['expires_at'] = null;
            }

            $this->Coupon_model->update_coupon($id, $coupon_data);

            $this->session->set_flashdata('success', 'Coupon updated successfully');
            redirect('coupons');
        }
    }

    public function delete($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $coupon = $this->Coupon_model->get_coupon($id);

        if (empty($coupon)) {
            show_404();
        }

        $this->Coupon_model->delete_coupon($id);

        $this->session->set_flashdata('success', 'Coupon deleted successfully');
        redirect('coupons');
    }
}
