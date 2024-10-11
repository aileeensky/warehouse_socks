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
    $routes->get('stock', 'MonitoringController::stock');
    $routes->post('inputjalur', 'MonitoringController::inputJalur');
});

//gudang
$routes->group('/gudang', ['filter' => 'gudang'], function ($routes) {
    $routes->get('', 'GudangController::index');
    $routes->get('inputdatabase', 'GudangController::inputNoModel');
    $routes->post('importdatabase', 'GudangController::importDatabase');
    $routes->get('stock', 'GudangController::stock');
    $routes->get('stockmodal/(:num)', 'GudangController::getStockModal/$1');
    $routes->post('inputstock', 'GudangController::inputStock');
    $routes->get('detailstock/(:any)', 'GudangController::detailStock/$1');
    $routes->get('datapermintaan', 'GudangController::dataPermintaan');
    $routes->get('dataterkirim', 'GudangController::dataTerkirim');
    $routes->get('reportpemasukan', 'GudangController::reportPemasukan');
    $routes->get('reportpengeluaran', 'GudangController::reportPengeluaran');
});

//packing
$routes->group('/packing', ['filter' => 'packing'], function ($routes) {
    $routes->get('', 'PackingController::index');
    $routes->get('stock', 'PackingController::stock');
    $routes->post('inputpermintaan', 'PackingController::inputPermintaan');
    $routes->get('schedule', 'PackingController::schedulePacking');
});
