<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->model('Cart_model');
        $this->load->helper('url');
        $this->load->library('session');

        // Verificar se o usuário está logado
        if ($this->session->userdata('logged_in')) {
            $this->load->model('User_model');
            $this->user = $this->User_model->get_user_by_id($this->session->userdata('user_id'));
        } else {
            $this->user = null;
        }
    }

    public function index() {
        $data['title'] = 'Shop';
        $data['featured_products'] = $this->Product_model->get_featured_products(8);

        // Adicionar informações de estoque
        foreach ($data['featured_products'] as &$product) {
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

        // Adicionar contagem de itens no carrinho
        if ($this->user) {
            $data['cart_count'] = $this->Cart_model->get_cart_count($this->user['id']);
        } else {
            $data['cart_count'] = 0;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('shop/index', $data);
        $this->load->view('templates/footer');
    }

    public function products() {
        $data['title'] = 'All Products';
        $data['products'] = $this->Product_model->get_products();

        // Adicionar informações de estoque
        foreach ($data['products'] as &$product) {
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

        // Adicionar contagem de itens no carrinho
        if ($this->user) {
            $data['cart_count'] = $this->Cart_model->get_cart_count($this->user['id']);
        } else {
            $data['cart_count'] = 0;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('shop/products', $data);
        $this->load->view('templates/footer');
    }

    public function product($id) {
        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            show_404();
        }

        $data['title'] = $product['name'];
        $data['product'] = $product;
        $data['variations'] = $this->Product_model->get_variations($id);

        // Obter informações de estoque
        if (count($data['variations']) > 0) {
            foreach ($data['variations'] as &$variation) {
                $variation['stock'] = $this->Stock_model->get_stock($id, $variation['id']);
            }
        } else {
            $data['stock'] = $this->Stock_model->get_stock($id);
        }

        // Adicionar contagem de itens no carrinho
        if ($this->user) {
            $data['cart_count'] = $this->Cart_model->get_cart_count($this->user['id']);
        } else {
            $data['cart_count'] = 0;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('shop/product', $data);
        $this->load->view('templates/footer');
    }

    public function search() {
        $keyword = $this->input->get('keyword');

        if (!$keyword) {
            redirect('shop/products');
        }

        $data['title'] = 'Search Results: ' . $keyword;
        $data['keyword'] = $keyword;
        $data['products'] = $this->Product_model->search_products($keyword);

        // Adicionar informações de estoque
        foreach ($data['products'] as &$product) {
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

        // Adicionar contagem de itens no carrinho
        if ($this->user) {
            $data['cart_count'] = $this->Cart_model->get_cart_count($this->user['id']);
        } else {
            $data['cart_count'] = 0;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('shop/search', $data);
        $this->load->view('templates/footer');
    }
}
