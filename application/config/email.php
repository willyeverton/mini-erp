<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.gmail.com'; // Altere para seu servidor SMTP
$config['smtp_port'] = 587;
$config['smtp_user'] = 'seu_email@gmail.com'; // Altere para seu email
$config['smtp_pass'] = 'sua_senha_de_app'; // Altere para sua senha
$config['smtp_crypto'] = 'tls';
$config['charset'] = 'utf-8';
$config['mailtype'] = 'html';
$config['newline'] = "\r\n";
