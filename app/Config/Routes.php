<?php

namespace Config;

use Config\Services;

$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('ReservationController');
$routes->setDefaultMethod('index');
$routes->setAutoRoute(false);

// 기본 홈
$routes->get('/', 'ReservationController::index');

// 사용자 페이지
$routes->get('/view/reserve', 'ReservationController::viewReserve');
$routes->get('/view/admin', 'ReservationController::viewAdmin');
$routes->get('/view/find', 'ReservationController::viewFind'); // ✅ 추가됨

// 예약 관련 API
$routes->post('/api/reservations/request-code', 'ReservationController::requestCode');
$routes->post('/api/reservations/verify-code', 'ReservationController::verifyCode');
$routes->post('/api/reservations/create', 'ReservationController::create');
$routes->get('/api/reservations/find', 'ReservationController::findReservations'); // ✅ 추가됨

// 관리자 기능 (한글 이름/공백 포함 안전 처리)
$routes->get('/admin/update-member/(:num)/(:segment)/(:segment)', 'ReservationController::adminUpdateMemberGet/$1/$2/$3');
$routes->get('/admin/update-price/(:num)/(:num)', 'ReservationController::adminUpdatePriceGet/$1/$2');
$routes->get('/admin/delete/(:num)', 'ReservationController::adminDeleteReservation/$1');
