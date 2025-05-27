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
        // Remover a restrição de acesso exclusivo para administradores
        // A autenticação geral já é verificada em MY_Controller

        // Configurar paginação
        $this->load->library('pagination');

        $config['base_url'] = site_url('products/index');
        $config['total_rows'] = $this->Product_model->count_products($this->input->get('search'));
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';

        // Estilo da paginação (Bootstrap)
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);

        // Obter página atual
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $offset = ($page - 1) * $config['per_page'];

        // Obter dados dos produtos com busca
        $data['products'] = $this->Product_model->get_products(
            $config['per_page'],
            $offset,
            $this->input->get('search')
        );

        $data['title'] = 'Products Management';
        $data['user'] = $this->user;
        $data['pagination'] = $this->pagination->create_links();
        $data['search'] = $this->input->get('search');

        // Registrar o componente de confirmação de exclusão
        load_component('confirmation-modal');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('products/index', $data);
        $this->load->view('templates/footer');
    }

    public function view($id) {
        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            show_404();
        }

        $data['title'] = $product['name'];
        $data['product'] = $product;
        $data['variations'] = $this->Product_model->get_variations($id);
        $data['user'] = $this->user;

        // Obter informações de estoque
        if (count($data['variations']) > 0) {
            foreach ($data['variations'] as &$variation) {
                $variation['stock'] = $this->Stock_model->get_stock($id, $variation['id']);
            }
        } else {
            $data['stock'] = $this->Stock_model->get_stock($id);
        }

        // Registrar scripts para esta página
        register_js('product-view', 'products');
        // Registrar o componente de confirmação de exclusão
        load_component('confirmation-modal');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('products/view', $data);
        $this->load->view('templates/footer');
    }

    public function create() {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $data['title'] = 'Create Product';

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            // Adicione o usuário aos dados
            $data['user'] = $this->user;
            register_js('product-form', 'products');

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('products/create', $data);
            $this->load->view('templates/footer');
        } else {

            $product_image = '';

            if ($_FILES['image']['size'] > 0) {
                $config['upload_path'] = './assets/images/products/';
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('image')) {
                    $image_data = $this->upload->data();
                    $product_image = $image_data['file_name'];
                }
            }

            $product_data = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'price' => $this->input->post('price'),
                'image' => $product_image,
                'featured' => $this->input->post('featured') ? 1 : 0
            );

            $product_id = $this->Product_model->create_product($product_data);

            // Processar variações, se houver
            $variations = $this->input->post('variations');
            $stocks = $this->input->post('stocks');

            if (!empty($variations)) {
                foreach ($variations as $key => $variation_name) {
                    if (!empty($variation_name)) {
                        $variation_id = $this->Product_model->add_variation($product_id, $variation_name);

                        // Adicionar estoque para a variação
                        if (isset($stocks[$key])) {
                            $this->Stock_model->update_stock($product_id, $variation_id, $stocks[$key]);
                        }
                    }
                }
            } else {
                // Se não houver variações, adicionar estoque para o produto principal
                $stock = $this->input->post('stock');
                if ($stock !== '') {
                    $this->Stock_model->update_stock($product_id, null, $stock);
                }
            }

            $this->session->set_flashdata('success', 'Product created successfully');
            redirect('products');
        }
    }

    public function edit($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            show_404();
        }

        $data['title'] = 'Edit Product';
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

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            // Adicione o usuário aos dados
            $data['user'] = $this->user;
            register_js('product-form', 'products');

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('products/edit', $data);
            $this->load->view('templates/footer');
        } else {
            // Upload de imagem, se houver
            $product_image = $product['image'];

            if ($_FILES['image']['size'] > 0) {
                $config['upload_path'] = './assets/images/products/';
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('image')) {
                    $image_data = $this->upload->data();
                    $product_image = $image_data['file_name'];
                }
            }

            $product_data = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'price' => $this->input->post('price'),
                'image' => $product_image,
                'featured' => $this->input->post('featured') ? 1 : 0
            );

            $this->Product_model->update_product($id, $product_data);

            // Atualizar estoque do produto principal
            $stock = $this->input->post('stock');
            if ($stock !== '' && count($data['variations']) == 0) {
                $this->Stock_model->update_stock($id, null, $stock);
            }

            // Atualizar variações existentes
            $variation_ids = $this->input->post('variation_ids');
            $variation_names = $this->input->post('variation_names');
            $variation_stocks = $this->input->post('variation_stocks');

            if (!empty($variation_ids)) {
                foreach ($variation_ids as $key => $variation_id) {
                    if (isset($variation_names[$key]) && !empty($variation_names[$key])) {
                        $this->Product_model->update_variation($variation_id, $variation_names[$key]);

                        if (isset($variation_stocks[$key])) {
                            $this->Stock_model->update_stock($id, $variation_id, $variation_stocks[$key]);
                        }
                    }
                }
            }

            // Adicionar novas variações
            $new_variations = $this->input->post('new_variations');
            $new_stocks = $this->input->post('new_stocks');

            if (!empty($new_variations)) {
                foreach ($new_variations as $key => $variation_name) {
                    if (!empty($variation_name)) {
                        $variation_id = $this->Product_model->add_variation($id, $variation_name);

                        if (isset($new_stocks[$key])) {
                            $this->Stock_model->update_stock($id, $variation_id, $new_stocks[$key]);
                        }
                    }
                }
            }

            $this->session->set_flashdata('success', 'Product updated successfully');
            redirect('products');
        }
    }

    public function delete($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $product = $this->Product_model->get_product($id);

        if (empty($product)) {
            show_404();
        }

        // Excluir produto usando o método safe_delete
        $result = $this->Product_model->delete_product($id);

        // Verificar o resultado da operação
        if (isset($result['success']) && $result['success']) {
            $this->session->set_flashdata('success', $result['message'] ?? 'Product deleted successfully');
        } else {
            $this->session->set_flashdata('error', $result['message'] ?? 'Error deleting product');
        }

        redirect('products');
    }

    public function delete_variation($id) {
        // Verificar permissões de administrador
        if (!$this->user || $this->user['role'] != 'admin') {
            redirect('auth');
        }

        $variation = $this->Product_model->get_variation($id);

        if (empty($variation)) {
            show_404();
        }

        $this->Product_model->delete_variation($id);

        $this->session->set_flashdata('success', 'Variation deleted successfully');
        redirect('products/edit/' . $variation['product_id']);
    }
}
