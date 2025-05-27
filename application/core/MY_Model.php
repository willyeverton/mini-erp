<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Classe base para modelos com funcionalidades estendidas
 */
class MY_Model extends CI_Model {

    /**
     * Realiza uma exclusão segura tratando erros de chave estrangeira
     *
     * @param string $table Nome da tabela
     * @param array|string $where Condição where
     * @return array ['success' => bool, 'message' => string]
     */
    protected function safe_delete($table, $where) {
        // Desabilitar temporariamente o display de erros
        $db_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        // Iniciar transação
        $this->db->trans_start();

        // Primeiro, verificar se o registro existe
        $this->db->where($where);
        $exists = $this->db->count_all_results($table) > 0;

        // Se o registro não existe, retornar imediatamente
        if (!$exists) {
            $this->db->db_debug = $db_debug;
            $this->db->trans_rollback();
            return [
                'success' => false,
                'message' => 'Registro não encontrado ou já excluído',
                'error_code' => 0
            ];
        }

        // Tentar excluir o registro
        $this->db->where($where);
        $this->db->delete($table);

        // Verificar se ocorreu algum erro
        $error = $this->db->error();

        // Restaurar configuração de debug
        $this->db->db_debug = $db_debug;

        // Finalizar transação
        $this->db->trans_complete();

        // Se houve erro de chave estrangeira (1451)
        if ($error['code'] == 1451) {
            // Extrair o nome da tabela relacionada da mensagem de erro
            preg_match('/foreign key constraint fails \(`[^`]+`\.`([^`]+)`/', $error['message'], $matches);
            $related_table = isset($matches[1]) ? $matches[1] : 'outra tabela';

            // Formatar nome da tabela para exibição amigável
            $friendly_table = ucfirst(str_replace('_', ' ', $related_table));

            return [
                'success' => false,
                'message' => "Não é possível excluir este registro porque está sendo utilizado em {$friendly_table}",
                'error_code' => $error['code']
            ];
        }
        // Se ocorreu outro tipo de erro
        else if ($error['code'] !== 0) {
            return [
                'success' => false,
                'message' => 'Erro ao excluir registro: ' . $error['message'],
                'error_code' => $error['code']
            ];
        }

        // Sucesso - se chegamos até aqui sem erros, a exclusão foi bem-sucedida
        return [
            'success' => true,
            'message' => 'Registro excluído com sucesso',
            'error_code' => 0
        ];
    }
}
