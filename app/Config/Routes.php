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
    $routes->get('inputdatabase', 'GudangController::inputNoModel');
    $routes->post('importdatabase', 'GudangController::importDatabase');
    $routes->get('stock', 'GudangController::stock');
    $routes->get('datapermintaan', 'GudangController::dataPermintaan');
    $routes->get('dataterkirim', 'GudangController::dataTerkirim');
    $routes->get('reportpemasukan', 'GudangController::reportPemasukan');
    $routes->get('reportpengeluaran', 'GudangController::reportPengeluaran');
});
