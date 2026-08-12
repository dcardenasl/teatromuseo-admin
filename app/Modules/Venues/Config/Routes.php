<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/venues', ['filter' => ['auth']], static function (RouteCollection $routes): void {
    // Venue
    $routes->get('venues', '\\App\\Modules\\Venues\\Controllers\\VenueController::index', ['as' => 'admin.venues.venues', 'filter' => 'permission:event.venues.read']);
    $routes->get('venues/data', '\\App\\Modules\\Venues\\Controllers\\VenueController::data', ['as' => 'admin.venues.venues.data', 'filter' => 'permission:event.venues.read']);
    $routes->get('venues/create', '\\App\\Modules\\Venues\\Controllers\\VenueController::create', ['as' => 'admin.venues.venues.create', 'filter' => 'permission:event.venues.write']);
    $routes->post('venues', '\\App\\Modules\\Venues\\Controllers\\VenueController::store', ['as' => 'admin.venues.venues.store', 'filter' => 'permission:event.venues.write']);
    $routes->get('venues/(:segment)', '\\App\\Modules\\Venues\\Controllers\\VenueController::show/$1', ['as' => 'admin.venues.venues.show', 'filter' => 'permission:event.venues.read']);
    $routes->get('venues/(:segment)/edit', '\\App\\Modules\\Venues\\Controllers\\VenueController::edit/$1', ['as' => 'admin.venues.venues.edit', 'filter' => 'permission:event.venues.write']);
    $routes->post('venues/(:segment)', '\\App\\Modules\\Venues\\Controllers\\VenueController::update/$1', ['as' => 'admin.venues.venues.update', 'filter' => 'permission:event.venues.write']);
    $routes->post('venues/(:segment)/delete', '\\App\\Modules\\Venues\\Controllers\\VenueController::delete/$1', ['as' => 'admin.venues.venues.delete', 'filter' => 'permission:event.venues.delete']);
});
