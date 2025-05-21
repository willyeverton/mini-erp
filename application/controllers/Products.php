<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    public function index() {
        $data['products'] = $this->Product_model->get_products();
        $data['title'] = 'Products List';

        $this->load->view('templates/header', $data);
        $this->load->view('products/index', $data);
        $this->load->view('templates/footer');
    }

    public function create() {
        $data['title'] = 'Create Product';

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('products/create', $data);
            $this->load->view('templates/footer');
        } else {
            // Upload da imagem, se houver
            $image = '';
            if ($_FILES['image']['name']) {
                $config['upload_path'] = './uploads/products/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;

                if (!file_exists($config['upload_path'])) {
                    mkdir($config['upload_path'], 0777, true);
                }

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);

                    $this->load->view('templates/header', $data);
                    $this->load->view('products/create', $data);
                    $this->load->view('templates/footer');
                    return;
                } else {
                    $upload_data = $this->upload->data();
                    $image = $upload_data['file_name'];
                }
            }

            // Salvar produto
            $product_data = array(
                'name' => $this->input->post('name'),
                'price' => $this->input->post('price'),
                'description' => $this->input->post('description'),
                'image' => $image
            );

            $product_id = $this->Product_model->create_product($product_data);

            // Processar variações, se houver
            $variation_names = $this->input->post('variation_name');
            $variation_stocks = $this->input->post('variation_stock');

            if ($variation_names && is_array($variation_names)) {
                foreach ($variation_names as $key => $name) {
                    if (!empty($name)) {
                        $variation_id = $this->Product_model->add_variation($product_id, $name);

                        // Adicionar estoque para a variação
                        if (isset($variation_stocks[$key])) {
                            $this->Stock_model->update_stock($product_id, $variation_id, $variation_stocks[$key]);
                        }
                    }
                }
            } else {
                // Se não houver variações, adicionar estoque para o produto principal
                $stock = $this->input->post('stock');
                if ($stock) {
                    $this->Stock_model->update_stock($product_id, null, $stock);
                }
            }

            $this->session->set_flashdata('success', 'Product created successfully');
            redirect('products');
        }
    }

    public function edit($id) {
        $data['product'] = $this->Product_model->get_product($id);

        if (empty($data['product'])) {
            show_404();
        }

        $data['variations'] = $this->Product_model->get_variations($id);
        $data['title'] = 'Edit Product';

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('products/edit', $data);
            $this->load->view('templates/footer');
        } else {
            // Upload da imagem, se houver
            $image = $data['product']['image'];
            if ($_FILES['image']['name']) {
                $config['upload_path'] = './uploads/products/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;

                if (!file_exists($config['upload_path'])) {
                    mkdir($config['upload_path'], 0777, true);
                }

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);

                    $this->load->view('templates/header', $data);
                    $this->load->view('products/edit', $data);
                    $this->load->view('templates/footer');
                    return;
                } else {
                    // Remover imagem antiga, se existir
                    if ($image && file_exists('./uploads/products/' . $image)) {
                        unlink('./uploads/products/' . $image);
                    }

                    $upload_data = $this->upload->data();
                    $image = $upload_data['file_name'];
                }
            }

            // Atualizar produto
            $product_data = array(
                'name' => $this->input->post('name'),
                'price' => $this->input->post('price'),
                'description' => $this->input->post('description'),
                'image' => $image
            );

            $this->Product_model->update_product($id, $product_data);

            // Atualizar estoque do produto principal
            $stock = $this->input->post('stock');
            if ($stock !== false) {
                $this->Stock_model->update_stock($id, null, $stock);
            }

            // Atualizar variações existentes
            $existing_variation_ids = $this->input->post('variation_id');
            $existing_variation_names = $this->input->post('existing_variation_name');
            $existing_variation_stocks = $this->input->post('existing_variation_stock');

            if ($existing_variation_ids && is_array($existing_variation_ids)) {
                foreach ($existing_variation_ids as $key => $variation_id) {
                    $this->Product_model->update_variation($variation_id, $existing_variation_names[$key]);
                    $this->Stock_model->update_stock($id, $variation_id, $existing_variation_stocks[$key]);
                }
            }

            // Adicionar novas variações
            $variation_names = $this->input->post('variation_name');
            $variation_stocks = $this->input->post('variation_stock');

            if ($variation_names && is_array($variation_names)) {
                foreach ($variation_names as $key => $name) {
                    if (!empty($name)) {
                        $variation_id = $this->Product_model->add_variation($id, $name);

                        // Adicionar estoque para a variação
                        if (isset($variation_stocks[$key])) {
                            $this->Stock_model->update_stock($id, $variation_id, $variation_stocks[$key]);
                        }
                    }
                }
            }

            $this->session->set_flashdata('success', 'Product updated successfully');
            redirect('products');
        }
    }

    public function delete($id) {
        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            show_404();
        }

        // Remover imagem, se existir
        if ($product['image'] && file_exists('./uploads/products/' . $product['image'])) {
            unlink('./uploads/products/' . $product['image']);
        }

        $this->Product_model->delete_product($id);
        $this->session->set_flashdata('success', 'Product deleted successfully');
        redirect('products');
    }

    public function buy($id) {
        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            show_404();
        }

        $variations = $this->Product_model->get_variations($id);

        // Adicionar ao carrinho
        $variation_id = $this->input->post('variation_id');
        $quantity = $this->input->post('quantity') ? $this->input->post('quantity') : 1;

        // Verificar estoque
        $stock_available = $this->Stock_model->check_stock($id, $variation_id, $quantity);

        if (!$stock_available) {
            $this->session->set_flashdata('error', 'Insufficient stock');
            redirect('products/view/' . $id);
        }

        // Obter carrinho atual
        $cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : array();

        // Gerar chave única para o item (produto + variação)
        $item_key = $id . '-' . ($variation_id ? $variation_id : '0');

        // Se o item já existe no carrinho, atualizar quantidade
        if (isset($cart[$item_key])) {
            $cart[$item_key]['quantity'] += $quantity;
        } else {
            // Adicionar novo item ao carrinho
            $variation_name = '';
            if ($variation_id) {
                foreach ($variations as $var) {
                    if ($var['id'] == $variation_id) {
                        $variation_name = $var['name'];
                        break;
                    }
                }
            }

            $cart[$item_key] = array(
                'product_id' => $id,
                'variation_id' => $variation_id,
                'name' => $product['name'],
                'variation_name' => $variation_name,
                'price' => $product['price'],
                'quantity' => $quantity,
                'subtotal' => $product['price'] * $quantity
            );
        }

        // Atualizar carrinho na sessão
        $this->session->set_userdata('cart', $cart);

        $this->session->set_flashdata('success', 'Product added to cart');
        redirect('cart');
    }

    public function view($id) {
        $data['product'] = $this->Product_model->get_product($id);

        if (empty($data['product'])) {
            show_404();
        }

        $data['variations'] = $this->Product_model->get_variations($id);
        $data['title'] = $data['product']['name'];

        $this->load->view('templates/header', $data);
        $this->load->view('products/view', $data);
        $this->load->view('templates/footer');
    }
}
