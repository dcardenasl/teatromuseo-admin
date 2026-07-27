<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/events', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Event
    $routes->get('events', '\\App\\Modules\\Events\\Controllers\\EventController::index', ['as' => 'admin.events.events']);
    $routes->get('events/data', '\\App\\Modules\\Events\\Controllers\\EventController::data', ['as' => 'admin.events.events.data']);
    $routes->get('events/create', '\\App\\Modules\\Events\\Controllers\\EventController::create', ['as' => 'admin.events.events.create']);
    $routes->post('events', '\\App\\Modules\\Events\\Controllers\\EventController::store', ['as' => 'admin.events.events.store']);
    $routes->get('events/(:segment)', '\\App\\Modules\\Events\\Controllers\\EventController::show/$1', ['as' => 'admin.events.events.show']);
    $routes->get('events/(:segment)/edit', '\\App\\Modules\\Events\\Controllers\\EventController::edit/$1', ['as' => 'admin.events.events.edit']);
    $routes->post('events/(:segment)', '\\App\\Modules\\Events\\Controllers\\EventController::update/$1', ['as' => 'admin.events.events.update']);
    $routes->post('events/(:segment)/delete', '\\App\\Modules\\Events\\Controllers\\EventController::delete/$1', ['as' => 'admin.events.events.delete']);



});
