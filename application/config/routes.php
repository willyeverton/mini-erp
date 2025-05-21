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

// Rotas da API
$route['api/token'] = 'auth/token';
$route['api/products'] = 'api/products';
$route['api/products/(:num)'] = 'api/products/view/$1';
$route['api/cart'] = 'api/cart';
$route['api/cart/add'] = 'api/cart/add';
$route['api/cart/update'] = 'api/cart/update';
$route['api/cart/remove/(:num)'] = 'api/cart/remove/$1';
$route['api/orders'] = 'api/orders';
$route['api/orders/(:num)'] = 'api/orders/view/$1';
$route['api/users/profile'] = 'api/users/profile';
$route['api/users/update'] = 'api/users/update';
