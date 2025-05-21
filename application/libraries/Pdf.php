<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

class Pdf {
    public $dompdf;

    public function __construct() {
        // Incluir o autoloader do Dompdf
        require_once APPPATH . 'third_party/dompdf/autoload.inc.php';

        // Inicializar Dompdf
        $this->dompdf = new Dompdf();
    }

    public function loadHtml($html) {
        $this->dompdf->loadHtml($html);
    }

    public function setPaper($paper, $orientation) {
        $this->dompdf->setPaper($paper, $orientation);
    }

    public function render() {
        $this->dompdf->render();
    }

    public function stream($filename, $options = array()) {
        $this->dompdf->stream($filename, $options);
    }

    public function output() {
        return $this->dompdf->output();
    }
}
