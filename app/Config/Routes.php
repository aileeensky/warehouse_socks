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
    $routes->get('detailstock/(:any)', 'MonitoringController::detailStock/$1');
    $routes->post('inputjalur', 'MonitoringController::inputJalur');

    $routes->get('editpemasukan', 'MonitoringController::editPemasukan');
    $routes->post('editpemasukan', 'MonitoringController::editPemasukan');
    $routes->get('editpengeluaran', 'MonitoringController::reportPengeluaran');
    $routes->post('editpengeluaran', 'MonitoringController::reportPengeluaran');

    //report
    $routes->get('excelstockgudang', 'ExcelController::excelStockGudang');
    $routes->post('excelreportpemasukan', 'ExcelController::excelReportPemasukan');
});

//gudang
$routes->group('/gudang', ['filter' => 'gudang'], function ($routes) {
    $routes->get('', 'GudangController::index');
    $routes->get('dataorder', 'GudangController::dataOrder');
    $routes->post('importdataorder', 'GudangController::importDataOrder');
    $routes->get('stock', 'GudangController::stock');
    $routes->post('importstock', 'GudangController::importStock');

    $routes->get('inputstockcluster', 'GudangController::inputStockCluster');
    $routes->get('stockmodal/(:num)', 'GudangController::getStockModal/$1');
    $routes->post('inputstock', 'GudangController::inputStock');
    $routes->get('detailstock/(:any)', 'GudangController::detailStock/$1');
    $routes->get('datapermintaan', 'GudangController::dataPermintaan');
    $routes->post('datapermintaan', 'GudangController::dataPermintaan');
    $routes->post('inputpengeluaran', 'GudangController::inputPengeluaran');
    $routes->get('dataterkirim', 'GudangController::dataTerkirim');
    $routes->post('dataterkirim', 'GudangController::dataTerkirim');
    $routes->get('reportpemasukan', 'GudangController::reportPemasukan');
    $routes->post('reportpemasukan', 'GudangController::reportPemasukan');
    $routes->get('reportpengeluaran', 'GudangController::reportPengeluaran');
    $routes->post('reportpengeluaran', 'GudangController::reportPengeluaran');
    $routes->post('getStockByIdAnak', 'GudangController::getStockByIdAnak');

    //report
    $routes->get('exceldataorder', 'ExcelController::excelDataOrder');
    $routes->get('excelstockgudang', 'ExcelController::excelStockGudang');
    $routes->post('excelreportpemasukan', 'ExcelController::excelReportPemasukan');
    $routes->post('excelreportpengeluaran', 'ExcelController::excelReportPengeluaran');
});

//packing
$routes->group('/packing', ['filter' => 'packing'], function ($routes) {
    $routes->get('', 'PackingController::index');
    $routes->get('stock', 'PackingController::stock');
    $routes->post('stock', 'PackingController::stock');
    $routes->post('inputpermintaan', 'PackingController::inputPermintaan');
    $routes->get('schedule', 'PackingController::schedulePacking');
    $routes->post('schedule', 'PackingController::schedulePacking');
    $routes->post('kirimpermintaan', 'PackingController::kirimSchedule');
    $routes->post('hapuspermintaan', 'PackingController::hapusPermintaan');
    $routes->get('statuspermintaan', 'PackingController::statusPermintaan');
    $routes->post('statuspermintaan', 'PackingController::statusPermintaan');

    //report
    $routes->post('excelreportstock', 'ExcelController::excelReportStock');
    $routes->post('excel-status-permintaan', 'ExcelController::excelSatusPermintaan');
});


// user
$routes->group('/user', ['filter' => 'user'], function ($routes) {
    $routes->get('', 'UserController::index');
    $routes->get('stock', 'UserController::stock');
    $routes->get('reportpemasukan', 'UserController::reportPemasukan');
    $routes->post('reportpemasukan', 'UserController::reportPemasukan');
    $routes->get('reportpermintaan', 'UserController::reportPermintaan');
    $routes->post('reportpermintaan', 'UserController::reportPermintaan');
    $routes->get('reportpengeluaran', 'UserController::reportPengeluaran');
    $routes->post('reportpengeluaran', 'UserController::reportPengeluaran');

    //report
    $routes->get('exceldataorder', 'ExcelController::excelDataOrder');
    $routes->get('excelstockgudang', 'ExcelController::excelStockGudang');
    $routes->post('excelreportpemasukan', 'ExcelController::excelReportPemasukan');
    $routes->post('excelreportpermintaan', 'ExcelController::excelSatusPermintaan');
    $routes->post('excelreportpengeluaran', 'ExcelController::excelReportPengeluaran');
});
