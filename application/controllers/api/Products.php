<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Products extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Stock_model');

        // Verificar autenticação para métodos que requerem
        if ($this->router->method !== 'index_get' && $this->router->method !== 'view_get') {
            $this->verify_token();
        }
    }

    public function index_get() {
        $featured = $this->get('featured');

        if ($featured) {
            $products = $this->Product_model->get_featured_products();
        } else {
            $products = $this->Product_model->get_products();
        }

        // Adicionar URLs completas para imagens
        foreach ($products as &$product) {
            $product['image_url'] = base_url('assets/images/products/' . $product['image']);
        }

        $this->response($products, REST_Controller::HTTP_OK);
    }

    public function view_get($id) {
        $product = $this->Product_model->get_product($id);

        if (!$product) {
            $this->response(['error' => 'Product not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Adicionar URL completa para imagem
        $product['image_url'] = base_url('assets/images/products/' . $product['image']);

        // Obter variações
        $variations = $this->Product_model->get_variations($id);

        // Obter informações de estoque
        if (count($variations) > 0) {
            foreach ($variations as &$variation) {
                $variation['stock'] = $this->Stock_model->get_stock($id, $variation['id']);
            }
            $product['variations'] = $variations;
        } else {
            $product['stock'] = $this->Stock_model->get_stock($id);
        }

        $this->response($product, REST_Controller::HTTP_OK);
    }

    public function create_post() {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to create products'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $data = $this->post();

        // Validação básica
        if (!isset($data['name']) || !isset($data['price'])) {
            $this->response(['error' => 'Name and price are required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $product_data = array(
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => isset($data['description']) ? $data['description'] : ''
        );

        $product_id = $this->Product_model->create_product($product_data);

        // Processar variações, se houver
        if (isset($data['variations']) && is_array($data['variations'])) {
            foreach ($data['variations'] as $variation) {
                if (isset($variation['name'])) {
                    $variation_id = $this->Product_model->add_variation($product_id, $variation['name']);

                    // Adicionar estoque para a variação
                    if (isset($variation['stock'])) {
                        $this->Stock_model->update_stock($product_id, $variation_id, $variation['stock']);
                    }
                }
            }
        } else if (isset($data['stock'])) {
            // Se não houver variações, adicionar estoque para o produto principal
            $this->Stock_model->update_stock($product_id, null, $data['stock']);
        }

        $product = $this->Product_model->get_product($product_id);
        $this->response($product, REST_Controller::HTTP_CREATED);
    }

    public function update_put($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to update products'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $product = $this->Product_model->get_product($id);

        if (!$product) {
            $this->response(['error' => 'Product not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $data = $this->put();

        $product_data = array();

        if (isset($data['name'])) {
            $product_data['name'] = $data['name'];
        }

        if (isset($data['price'])) {
            $product_data['price'] = $data['price'];
        }

        if (isset($data['description'])) {
            $product_data['description'] = $data['description'];
        }

        if (isset($data['featured'])) {
            $product_data['featured'] = $data['featured'] ? 1 : 0;
        }

        if (!empty($product_data)) {
            $this->Product_model->update_product($id, $product_data);
        }

        // Atualizar estoque, se fornecido
        if (isset($data['stock'])) {
            $this->Stock_model->update_stock($id, null, $data['stock']);
        }

        $updated_product = $this->Product_model->get_product($id);
        $this->response($updated_product, REST_Controller::HTTP_OK);
    }

    public function delete_delete($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'You do not have permission to delete products'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $product = $this->Product_model->get_product($id);

        if (!$product) {
            $this->response(['error' => 'Product not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $this->Product_model->delete_product($id);

        $this->response(['success' => 'Product deleted successfully'], REST_Controller::HTTP_OK);
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
