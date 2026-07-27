<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/venues', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Venue
    $routes->get('venues', '\\App\\Modules\\Venues\\Controllers\\VenueController::index', ['as' => 'admin.venues.venues']);
    $routes->get('venues/data', '\\App\\Modules\\Venues\\Controllers\\VenueController::data', ['as' => 'admin.venues.venues.data']);
    $routes->get('venues/create', '\\App\\Modules\\Venues\\Controllers\\VenueController::create', ['as' => 'admin.venues.venues.create']);
    $routes->post('venues', '\\App\\Modules\\Venues\\Controllers\\VenueController::store', ['as' => 'admin.venues.venues.store']);
    $routes->get('venues/(:segment)', '\\App\\Modules\\Venues\\Controllers\\VenueController::show/$1', ['as' => 'admin.venues.venues.show']);
    $routes->get('venues/(:segment)/edit', '\\App\\Modules\\Venues\\Controllers\\VenueController::edit/$1', ['as' => 'admin.venues.venues.edit']);
    $routes->post('venues/(:segment)', '\\App\\Modules\\Venues\\Controllers\\VenueController::update/$1', ['as' => 'admin.venues.venues.update']);
    $routes->post('venues/(:segment)/delete', '\\App\\Modules\\Venues\\Controllers\\VenueController::delete/$1', ['as' => 'admin.venues.venues.delete']);



});
