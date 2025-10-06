<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Config\Services;

$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('ReservationController');
$routes->setDefaultMethod('index');
$routes->setAutoRoute(false);

// 기본 홈
$routes->get('/', 'ReservationController::index');

// --- 예약 관련 (사용자용) ---
$routes->post('/api/reservations/create', 'ReservationController::create');
$routes->post('/api/reservations/request-code', 'ReservationController::requestCode');
$routes->post('/api/reservations/confirm', 'ReservationController::confirm');
$routes->get('/api/reservations/find', 'ReservationController::find');

// --- 관리자용 (GET URL 직접 호출) ---
$routes->get('/admin/update-price/(:num)/(:num)', 'ReservationController::adminUpdatePriceGet/$1/$2');
$routes->get('/admin/update-member/(:num)/(:any)/(:any)', 'ReservationController::adminUpdateMemberGet/$1/$2/$3');

// --- View 화면 ---
$routes->get('/view/reserve', 'ReservationController::viewReserve');
$routes->get('/view/find', 'ReservationController::viewFind');

// --- 관리자용 (뷰 + CRUD) ---
$routes->get('/view/admin', 'ReservationController::viewAdmin');
$routes->get('/admin/update-price/(:num)/(:num)', 'ReservationController::adminUpdatePriceGet/$1/$2');
$routes->get('/admin/update-member/(:num)/(:any)/(:any)', 'ReservationController::adminUpdateMemberGet/$1/$2/$3');
$routes->get('/admin/delete/(:num)', 'ReservationController::adminDeleteReservation/$1');
