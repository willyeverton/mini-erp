<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper para gerenciamento de assets (CSS e JavaScript)
 */

if (!function_exists('load_js')) {
    /**
     * Carrega um ou mais arquivos JavaScript
     *
     * @param string|array $files Nome do arquivo ou array de arquivos
     * @param string $module Módulo/pasta onde o arquivo está localizado
     * @return string HTML para incluir os scripts
     */
    function load_js($files, $module = '') {
        $CI =& get_instance();
        $output = '';

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // Adicionar extensão .js se não estiver presente
            if (!preg_match('/\.js$/', $file)) {
                $file .= '.js';
            }

            // Construir caminho do arquivo
            $path = 'assets/js/';
            if ($module) {
                $path .= $module . '/';
            }

            $output .= '<script src="' . base_url($path . $file) . '"></script>' . PHP_EOL;
        }

        return $output;
    }
}

if (!function_exists('load_css')) {
    /**
     * Carrega um ou mais arquivos CSS
     *
     * @param string|array $files Nome do arquivo ou array de arquivos
     * @param string $module Módulo/pasta onde o arquivo está localizado
     * @return string HTML para incluir os estilos
     */
    function load_css($files, $module = '') {
        $CI =& get_instance();
        $output = '';

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // Adicionar extensão .css se não estiver presente
            if (!preg_match('/\.css$/', $file)) {
                $file .= '.css';
            }

            // Construir caminho do arquivo
            $path = 'assets/css/';
            if ($module) {
                $path .= $module . '/';
            }

            $output .= '<link rel="stylesheet" href="' . base_url($path . $file) . '">' . PHP_EOL;
        }

        return $output;
    }
}

if (!function_exists('register_js')) {
    /**
     * Registra scripts JavaScript para carregar no footer
     *
     * @param string|array $files Nome do arquivo ou array de arquivos
     * @param string $module Módulo/pasta onde o arquivo está localizado
     */
    function register_js($files, $module = '') {
        $CI =& get_instance();

        if (!isset($CI->registered_js)) {
            $CI->registered_js = [];
        }

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $CI->registered_js[] = [
                'file' => $file,
                'module' => $module
            ];
        }
    }
}

if (!function_exists('get_registered_js')) {
    /**
     * Retorna HTML para todos os scripts JavaScript registrados
     *
     * @return string HTML para incluir os scripts
     */
    function get_registered_js() {
        $CI =& get_instance();
        $output = '';

        if (isset($CI->registered_js) && is_array($CI->registered_js)) {
            foreach ($CI->registered_js as $js) {
                $output .= load_js($js['file'], $js['module']);
            }
        }

        return $output;
    }
}

if (!function_exists('load_component')) {
    /**
     * Carrega um componente (HTML, CSS e JS)
     *
     * @param string $component Nome do componente
     * @return void
     */
    function load_component($component) {
        $CI =& get_instance();

        // Registrar o componente para renderização posterior
        if (!isset($CI->components)) {
            $CI->components = [];
        }

        if (!in_array($component, $CI->components)) {
            $CI->components[] = $component;

            // Carregar CSS do componente se existir
            if (file_exists(FCPATH . 'assets/css/components/' . $component . '.css')) {
                load_css($component, 'components');
            }

            // Registrar JS do componente para carregar no footer
            if (file_exists(FCPATH . 'assets/js/components/' . $component . '.js')) {
                register_js($component, 'components');
            }
        }
    }
}

if (!function_exists('render_components')) {
    /**
     * Renderiza o HTML de todos os componentes registrados
     *
     * @return string HTML de todos os componentes
     */
    function render_components() {
        $CI =& get_instance();
        $output = '';

        if (isset($CI->components) && is_array($CI->components)) {
            foreach ($CI->components as $component) {
                // Converter nome com hífen para underscore para compatibilidade com CI
                $view_name = str_replace('-', '_', $component);
                $view_path = 'components/' . $view_name;

                if (file_exists(APPPATH . 'views/' . $view_path . '.php')) {
                    $output .= $CI->load->view($view_path, [], TRUE);
                }
            }
        }

        return $output;
    }
}
