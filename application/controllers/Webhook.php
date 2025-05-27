<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/API_Controller.php';

class Webhook extends API_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Stock_model');
    }

    /**
     * Endpoint para receber atualizações de status de pedidos
     */
    public function order_status() {
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json_response(['error' => 'Method not allowed'], 405);
        }

        // Obter o corpo da requisição
        $data = $this->get_json_input();

        // Log da requisição para debug
        $this->_log_webhook_request(json_encode($data));

        // Validar dados recebidos
        if (!isset($data['order_id']) || !isset($data['status'])) {
            return $this->json_response(['error' => 'Missing required fields'], 400);
        }

        $order_id = $data['order_id'];
        $status = $data['status'];

        // Verificar se o pedido existe
        $order = $this->Order_model->get_order($order_id);
        if (!$order) {
            return $this->json_response(['error' => 'Order not found'], 404);
        }

        // Processar baseado no status
        if ($status === 'canceled') {
            // Se o status for cancelado, tratar cancelamento
            $this->_handle_order_cancellation($order);
            $message = 'Order canceled and updated successfully';
        } else {
            // Atualizar o status do pedido
            $this->Order_model->update_order($order_id, ['status' => $status]);
            $message = 'Order status updated successfully';
        }

        // Responder com sucesso
        return $this->json_response([
            'success' => true,
            'message' => $message,
            'order_id' => $order_id,
            'status' => $status
        ]);
    }

    /**
     * Processa o cancelamento de um pedido
     */
    private function _handle_order_cancellation($order) {
        // Obter itens do pedido para restaurar estoque
        $items = $this->Order_model->get_order_items($order['id']);

        // Restaurar estoque
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
