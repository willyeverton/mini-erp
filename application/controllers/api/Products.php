<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Products extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Stock_model');

        // Verificar autenticação para métodos que exigem
        if ($this->router->method != 'index_get' && $this->router->method != 'view_get') {
            $this->verify_token();
        }
    }

    public function index_get() {
        $products = $this->Product_model->get_products();

        // Adicionar informações de estoque
        foreach ($products as &$product) {
            $variations = $this->Product_model->get_variations($product['id']);

            if (count($variations) > 0) {
                $product['variations'] = $variations;

                foreach ($product['variations'] as &$variation) {
                    $variation['stock'] = $this->Stock_model->get_stock($product['id'], $variation['id']);
                }
            } else {
                $product['stock'] = $this->Stock_model->get_stock($product['id']);
            }
        }

        $this->response($products, REST_Controller::HTTP_OK);
    }

    public function view_get($id) {
        $product = $this->Product_model->get_product($id);

        if (!$product) {
            $this->response(['error' => 'Product not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        // Adicionar variações e estoque
        $variations = $this->Product_model->get_variations($id);

        if (count($variations) > 0) {
            $product['variations'] = $variations;

            foreach ($product['variations'] as &$variation) {
                $variation['stock'] = $this->Stock_model->get_stock($id, $variation['id']);
            }
        } else {
            $product['stock'] = $this->Stock_model->get_stock($id);
        }

        $this->response($product, REST_Controller::HTTP_OK);
    }

    public function create_post() {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'Permission denied'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $data = $this->post();

        if (!isset($data['name']) || !isset($data['price'])) {
            $this->response(['error' => 'Name and price are required'], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        $product_data = array(
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => isset($data['description']) ? $data['description'] : '',
            'featured' => isset($data['featured']) ? $data['featured'] : 0
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
            $this->response(['error' => 'Permission denied'], REST_Controller::HTTP_FORBIDDEN);
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
            $product_data['featured'] = $data['featured'];
        }

        if (!empty($product_data)) {
            $this->Product_model->update_product($id, $product_data);
        }

        // Atualizar estoque do produto principal
        if (isset($data['stock'])) {
            $this->Stock_model->update_stock($id, null, $data['stock']);
        }

        // Atualizar variações existentes
        if (isset($data['variations']) && is_array($data['variations'])) {
            foreach ($data['variations'] as $variation) {
                if (isset($variation['id']) && isset($variation['name'])) {
                    $this->Product_model->update_variation($variation['id'], $variation['name']);

                    if (isset($variation['stock'])) {
                        $this->Stock_model->update_stock($id, $variation['id'], $variation['stock']);
                    }
                } else if (!isset($variation['id']) && isset($variation['name'])) {
                    // Adicionar nova variação
                    $variation_id = $this->Product_model->add_variation($id, $variation['name']);

                    if (isset($variation['stock'])) {
                        $this->Stock_model->update_stock($id, $variation_id, $variation['stock']);
                    }
                }
            }
        }

        $updated_product = $this->Product_model->get_product($id);

        // Adicionar variações e estoque
        $variations = $this->Product_model->get_variations($id);

        if (count($variations) > 0) {
            $updated_product['variations'] = $variations;

            foreach ($updated_product['variations'] as &$variation) {
                $variation['stock'] = $this->Stock_model->get_stock($id, $variation['id']);
            }
        } else {
            $updated_product['stock'] = $this->Stock_model->get_stock($id);
        }

        $this->response($updated_product, REST_Controller::HTTP_OK);
    }

    public function delete_delete($id) {
        // Verificar permissões de administrador
        if ($this->user['role'] != 'admin') {
            $this->response(['error' => 'Permission denied'], REST_Controller::HTTP_FORBIDDEN);
            return;
        }

        $product = $this->Product_model->get_product($id);

        if (!$product) {
            $this->response(['error' => 'Product not found'], REST_Controller::HTTP_NOT_FOUND);
            return;
        }

        $this->Product_model->delete_product($id);

        $this->response(null, REST_Controller::HTTP_NO_CONTENT);
    }

    private function verify_token() {
        $headers = $this->input->request_headers();

        if (!isset($headers['Authorization'])) {
            $this->response(['error' => 'Authorization header required'], REST_Controller::HTTP_UNAUTHORIZED);
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
