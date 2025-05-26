<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon_model extends CI_Model {

    private $table = 'coupons';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Obter todos os cupons com paginação e pesquisa
    public function get_coupons($limit = NULL, $offset = NULL, $search = NULL) {
        if ($search) {
            $this->db->like('code', $search);
            $this->db->or_like('description', $search);
        }

        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table, $limit, $offset)->result_array();
    }

    // Contar total de cupons (para paginação)
    public function count_coupons($search = NULL) {
        if ($search) {
            $this->db->like('code', $search);
            $this->db->or_like('description', $search);
        }

        return $this->db->count_all_results($this->table);
    }

    // Obter cupom pelo ID
    public function get_coupon_by_id($id) {
        $query = $this->db->get_where($this->table, array('id' => $id));
        return $query->row_array();
    }

    // Obter cupom pelo código
    public function get_coupon_by_code($code) {
        $this->db->where('LOWER(code)', strtolower($code));
        $this->db->where('active', 1);
        $this->db->where('(usage_limit > usage_count OR usage_limit = 0)');

        // Verificar validade
        $today = date('Y-m-d');
        $this->db->where("(expires_at >= '$today' OR expires_at IS NULL)");
        $this->db->where("(start_date <= '$today' OR start_date IS NULL)");

        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    // Criar novo cupom
    public function create_coupon($data) {
        return $this->db->insert($this->table, $data);
    }

    // Atualizar cupom
    public function update_coupon($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    // Excluir cupom
    public function delete_coupon($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    // Incrementar contagem de uso
    public function increment_usage_count($id) {
        $this->db->set('usage_count', 'usage_count + 1', FALSE);
        $this->db->where('id', $id);
        return $this->db->update($this->table);
    }

    // Obter cupons ativos
    public function get_active_coupons() {
        $today = date('Y-m-d');

        $this->db->where('active', 1);
        $this->db->where('(usage_limit > usage_count OR usage_limit = 0)');
        $this->db->where('(end_date >= ? OR end_date IS NULL)', $today);
        $this->db->where('(start_date <= ? OR start_date IS NULL)', $today);

        return $this->db->get($this->table)->result_array();
    }

    // Verificar se o cupom é válido
    public function validate_coupon($code, $total_amount = 0) {
        $coupon = $this->get_coupon_by_code($code);

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Cupom inválido ou expirado.'
            ];
        }

        // Verificar valor mínimo de compra
        if ($coupon['min_purchase'] > 0 && $total_amount < $coupon['min_purchase']) {
            return [
                'valid' => false,
                'message' => 'O valor mínimo para este cupom é R$ ' . number_format($coupon['min_purchase'], 2, ',', '.')
            ];
        }

        return [
            'valid' => true,
            'coupon' => $coupon
        ];
    }

    // Calcular desconto baseado no cupom
    public function calculate_discount($coupon, $total_amount) {
        if ($coupon['discount_type'] == 'percentage') {
            $discount = $total_amount * ($coupon['discount_amount'] / 100);

            // Verificar se há valor máximo de desconto
            if ($coupon['max_discount'] > 0 && $discount > $coupon['max_discount']) {
                $discount = $coupon['max_discount'];
            }
        } else {
            $discount = $coupon['discount_amount'];

            // Garantir que o desconto não seja maior que o total
            if ($discount > $total_amount) {
                $discount = $total_amount;
            }
        }

        return $discount;
    }

    // Método a ser adicionado no controlador de pedidos
    public function apply_coupon() {
        $this->load->model('Coupon_model');

        $coupon_code = $this->input->post('coupon_code');
        $cart_total = $this->cart->total();

        $result = $this->Coupon_model->validate_coupon($coupon_code, $cart_total);

        if ($result['valid']) {
            $coupon = $result['coupon'];
            $discount = $this->Coupon_model->calculate_discount($coupon, $cart_total);

            // Armazenar o cupom na sessão
            $this->session->set_userdata('coupon', [
                'id' => $coupon['id'],
                'code' => $coupon['code'],
                'discount' => $discount
            ]);

            $this->session->set_flashdata('success', 'Cupom aplicado com sucesso! Desconto: R$ ' . number_format($discount, 2, ',', '.'));
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('cart');
    }
}
