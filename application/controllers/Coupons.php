<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Coupon_model');
        $this->load->helper('url');
        $this->load->library('form_validation');

        // Verificar se o usuário é admin
        if (!$this->user || $this->user['role'] !== 'admin') {
            show_error('You are not authorized to access this page', 403);
        }
    }

    public function index() {
        $data['coupons'] = $this->Coupon_model->get_coupons();
        $data['title'] = 'Coupons Management';

        $this->load->view('templates/header', $data);
        $this->load->view('coupons/index', $data);
        $this->load->view('templates/footer');
    }

    public function create() {
        $data['title'] = 'Create Coupon';

        $this->form_validation->set_rules('code', 'Code', 'required|is_unique[coupons.code]');
        $this->form_validation->set_rules('discount', 'Discount', 'required|numeric');
        $this->form_validation->set_rules('type', 'Type', 'required');
        $this->form_validation->set_rules('minimum_value', 'Minimum Value', 'numeric');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');
        $this->form_validation->set_rules('end_date', 'End Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('coupons/create', $data);
            $this->load->view('templates/footer');
        } else {
            $coupon_data = array(
                'code' => strtoupper($this->input->post('code')),
                'discount' => $this->input->post('discount'),
                'type' => $this->input->post('type'),
                'minimum_value' => $this->input->post('minimum_value'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'active' => $this->input->post('active') ? 1 : 0
            );

            $this->Coupon_model->create_coupon($coupon_data);
            $this->session->set_flashdata('success', 'Coupon created successfully');
            redirect('coupons');
        }
    }

    public function edit($id) {
        $data['coupon'] = $this->Coupon_model->get_coupon($id);

        if (empty($data['coupon'])) {
            show_404();
        }

        $data['title'] = 'Edit Coupon';

        $this->form_validation->set_rules('code', 'Code', 'required');
        $this->form_validation->set_rules('discount', 'Discount', 'required|numeric');
        $this->form_validation->set_rules('type', 'Type', 'required');
        $this->form_validation->set_rules('minimum_value', 'Minimum Value', 'numeric');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');
        $this->form_validation->set_rules('end_date', 'End Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('coupons/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $coupon_data = array(
                'code' => strtoupper($this->input->post('code')),
                'discount' => $this->input->post('discount'),
                'type' => $this->input->post('type'),
                'minimum_value' => $this->input->post('minimum_value'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'active' => $this->input->post('active') ? 1 : 0
            );

            $this->Coupon_model->update_coupon($id, $coupon_data);
            $this->session->set_flashdata('success', 'Coupon updated successfully');
            redirect('coupons');
        }
    }

    public function delete($id) {
        $coupon = $this->Coupon_model->get_coupon($id);

        if (empty($coupon)) {
            show_404();
        }

        $this->Coupon_model->delete_coupon($id);
        $this->session->set_flashdata('success', 'Coupon deleted successfully');
        redirect('coupons');
    }
}
