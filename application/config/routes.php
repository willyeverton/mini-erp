<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Rotas padrão
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Rotas de autenticação
$route['register'] = 'auth/register';
$route['login'] = 'auth';
$route['logout'] = 'auth/logout';
$route['reset-password'] = 'auth/reset_password';
$route['new-password/(:any)'] = 'auth/new_password/$1';

// Rotas do Webhook
$route['webhook/order-status'] = 'webhook/order_status';
