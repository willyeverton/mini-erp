<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
    }

    public function index() {
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

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($products));
    }

    public function view($id) {
        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Product not found']));
            return;
        }

        $variations = $this->Product_model->get_variations($id);

        if (count($variations) > 0) {
            $product['variations'] = $variations;

            foreach ($product['variations'] as &$variation) {
                $variation['stock'] = $this->Stock_model->get_stock($id, $variation['id']);
            }
        } else {
            $product['stock'] = $this->Stock_model->get_stock($id);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($product));
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

        if (!isset($data['name']) || !isset($data['price'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Name and price are required']));
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

        $this->output
            ->set_status_header(201)
            ->set_content_type('application/json')
            ->set_output(json_encode($product));
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

        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Product not found']));
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

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
        $variations = $this->Product_model->get_variations($id);

        if (count($variations) > 0) {
            $updated_product['variations'] = $variations;

            foreach ($updated_product['variations'] as &$variation) {
                $variation['stock'] = $this->Stock_model->get_stock($id, $variation['id']);
            }
        } else {
            $updated_product['stock'] = $this->Stock_model->get_stock($id);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($updated_product));
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

        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Product not found']));
            return;
        }

        $this->Product_model->delete_product($id);

        $this->output
            ->set_status_header(204)
            ->set_content_type('application/json')
            ->set_output(json_encode(null));
    }
}
