<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

//auth
$routes->get('/', 'AuthController::index');
$routes->get('/login', 'AuthController::index');
$routes->post('/logout', 'AuthController::logout');
$routes->post('authverify', 'AuthController::login');


$routes->group('/gudang', ['filter' => 'gudang'], function ($routes) {
    $routes->get('', 'GudangController::index');
    $routes->get('inputnomodel', 'GudangController::inputNoModel');
});
