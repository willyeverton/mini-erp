<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config = array(
    'protocol'      => 'smtp',
    'smtp_host'     => 'smtp.mailtrap.io',
    'smtp_port'     => 2525,
    'smtp_user'     => 'edf81f6d31af91', // Substitua pelas credenciais do Mailtrap
    'smtp_pass'     => '6d824154999158',   // Substitua pelas credenciais do Mailtrap
    'smtp_crypto'   => '',
    'mailtype'      => 'html',
    'charset'       => 'utf-8',
    'newline'       => "\r\n",
    'wordwrap'      => TRUE,
    'validate'      => TRUE
);
