<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Auth');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// Auth Routes
$routes->get('/', 'Auth::index', ['filter' => 'guest']);
$routes->get('/login', 'Auth::index', ['filter' => 'guest']);
$routes->post('/login', 'Auth::login', ['filter' => 'guest']);
$routes->get('/register', 'Auth::register', ['filter' => 'guest']);
$routes->post('/processRegister', 'Auth::processRegister', ['filter' => 'guest']);
$routes->get('/logout', 'Auth::logout', ['filter' => 'auth']);

// Presensi Routes
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/presensi', 'Presensi::index');
    $routes->get('/presensi/edit/(:num)', 'Presensi::edit/$1');
    $routes->post('/presensi/update/(:num)', 'Presensi::update/$1');
    $routes->get('/presensi/delete/(:num)', 'Presensi::delete/$1');
    $routes->get('/presensi/exportCsv', 'Presensi::exportCsv');
    $routes->get('/presensi/scan', 'Presensi::scan');
    $routes->post('/presensi/scan-rfid', 'Presensi::scanRfid');
    $routes->post('/presensi/scan-rfid-device', 'Presensi::scanRfidDevice');
    $routes->get('/presensi/debug-user/(:segment)', 'Presensi::debugUser/$1');
    $routes->get('/presensi/check-user/(:segment)', 'Presensi::checkUserData/$1');

    // Jadwal Shift Routes
    $routes->get('/jadwal-shift', 'JadwalShift::index');
    $routes->get('/jadwal-shift/create', 'JadwalShift::create');
    $routes->post('/jadwal-shift/store', 'JadwalShift::store');
    $routes->get('/jadwal-shift/edit/(:num)', 'JadwalShift::edit/$1');
    $routes->post('/jadwal-shift/update/(:num)', 'JadwalShift::update/$1');
    $routes->get('/jadwal-shift/delete/(:num)', 'JadwalShift::delete/$1');

    // User Management Routes
    $routes->get('/users', 'Users::index');
    $routes->get('/users/create', 'Users::create');
    $routes->post('/users/store', 'Users::store');
    $routes->get('/users/edit/(:num)', 'Users::edit/$1');
    $routes->post('/users/update/(:num)', 'Users::update/$1');
    $routes->get('/users/delete/(:num)', 'Users::delete/$1');
});

// Dashboard Route
$routes->get('/dashboard', 'Dashboard::index');
