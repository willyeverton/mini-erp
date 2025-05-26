<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
    }

    /**
     * Endpoint para receber atualizações de status de pedidos
     * Espera receber um JSON com o ID do pedido e o novo status
     * Exemplo: {"order_id": 123, "status": "shipped"}
     */
    public function order_status() {
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Method not allowed']));
            return;
        }

        // Obter o corpo da requisição
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Log da requisição para debug
        $this->_log_webhook_request($json);

        // Validar dados recebidos
        if (!isset($data['order_id']) || !isset($data['status'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Missing required fields']));
            return;
        }

        $order_id = $data['order_id'];
        $status = $data['status'];

        // Verificar se o pedido existe
        $order = $this->Order_model->get_order($order_id);
        if (!$order) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Order not found']));
            return;
        }

        // Processar baseado no status
        if ($status === 'canceled') {
            // Se o status for cancelado, excluir o pedido
            $this->_handle_order_cancellation($order);
            $message = 'Order canceled and removed successfully';
        } else {
            // Atualizar o status do pedido
            $this->Order_model->update_order($order_id, ['status' => $status]);
            $message = 'Order status updated successfully';
        }

        // Responder com sucesso
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => $message,
                'order_id' => $order_id,
                'status' => $status
            ]));
    }

    /**
     * Processa o cancelamento de um pedido
     */
    private function _handle_order_cancellation($order) {
        // Obter itens do pedido para restaurar estoque
        $items = $this->Order_model->get_order_items($order['id']);

        // Restaurar estoque
        $this->load->model('Stock_model');
        foreach ($items as $item) {
            $this->Stock_model->increase_stock(
                $item['product_id'],
                $item['variation_id'],
                $item['quantity']
            );
        }

        // Atualizar o status para cancelado
        $this->Order_model->update_order($order['id'], ['status' => 'canceled']);
    }

    /**
     * Registra a requisição recebida para fins de depuração
     */
    private function _log_webhook_request($json) {
        $log_path = APPPATH . 'logs/webhooks/';

        // Criar diretório se não existir
        if (!file_exists($log_path)) {
            mkdir($log_path, 0755, true);
        }

        $log_file = $log_path . 'webhook_' . date('Y-m-d') . '.log';
        $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $json . PHP_EOL;

        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}
