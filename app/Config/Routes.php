<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

//auth
$routes->get('/', 'AuthController::index');
$routes->get('/login', 'AuthController::index');
$routes->get('/logout', 'AuthController::logout');
$routes->post('authverify', 'AuthController::login');

//monitoring
$routes->group('/monitoring', ['filter' => 'monitoring'], function ($routes) {
    $routes->get('', 'MonitoringController::index');
    $routes->get('account', 'MonitoringController::account');
    $routes->post('inputuser', 'MonitoringController::inputUser');
});

//gudang
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
