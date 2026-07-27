<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/bookings', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Booking
    $routes->get('bookings', '\\App\\Modules\\Bookings\\Controllers\\BookingController::index', ['as' => 'admin.bookings.bookings']);
    $routes->get('bookings/data', '\\App\\Modules\\Bookings\\Controllers\\BookingController::data', ['as' => 'admin.bookings.bookings.data']);
    $routes->get('bookings/create', '\\App\\Modules\\Bookings\\Controllers\\BookingController::create', ['as' => 'admin.bookings.bookings.create']);
    $routes->post('bookings', '\\App\\Modules\\Bookings\\Controllers\\BookingController::store', ['as' => 'admin.bookings.bookings.store']);
    $routes->get('bookings/(:segment)', '\\App\\Modules\\Bookings\\Controllers\\BookingController::show/$1', ['as' => 'admin.bookings.bookings.show']);
    $routes->get('bookings/(:segment)/edit', '\\App\\Modules\\Bookings\\Controllers\\BookingController::edit/$1', ['as' => 'admin.bookings.bookings.edit']);
    $routes->post('bookings/(:segment)', '\\App\\Modules\\Bookings\\Controllers\\BookingController::update/$1', ['as' => 'admin.bookings.bookings.update']);
    $routes->post('bookings/(:segment)/delete', '\\App\\Modules\\Bookings\\Controllers\\BookingController::delete/$1', ['as' => 'admin.bookings.bookings.delete']);



});
