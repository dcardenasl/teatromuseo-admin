<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/events', ['filter' => ['auth']], static function (RouteCollection $routes): void {
    // Event
    $routes->get('events', '\\App\\Modules\\Events\\Controllers\\EventController::index', ['as' => 'admin.events.events', 'filter' => 'permission:event.events.read']);
    $routes->get('events/data', '\\App\\Modules\\Events\\Controllers\\EventController::data', ['as' => 'admin.events.events.data', 'filter' => 'permission:event.events.read']);
    $routes->get('events/create', '\\App\\Modules\\Events\\Controllers\\EventController::create', ['as' => 'admin.events.events.create', 'filter' => 'permission:event.events.write']);
    $routes->post('events', '\\App\\Modules\\Events\\Controllers\\EventController::store', ['as' => 'admin.events.events.store', 'filter' => 'permission:event.events.write']);
    $routes->get('events/(:segment)', '\\App\\Modules\\Events\\Controllers\\EventController::show/$1', ['as' => 'admin.events.events.show', 'filter' => 'permission:event.events.read']);
    $routes->get('events/(:segment)/edit', '\\App\\Modules\\Events\\Controllers\\EventController::edit/$1', ['as' => 'admin.events.events.edit', 'filter' => 'permission:event.events.write']);
    $routes->post('events/(:segment)', '\\App\\Modules\\Events\\Controllers\\EventController::update/$1', ['as' => 'admin.events.events.update', 'filter' => 'permission:event.events.write']);
    $routes->post('events/(:segment)/delete', '\\App\\Modules\\Events\\Controllers\\EventController::delete/$1', ['as' => 'admin.events.events.delete', 'filter' => 'permission:event.events.delete']);

    $routes->get('event-types', '\\App\\Modules\\Events\\Controllers\\EventTypeController::index', ['as' => 'admin.events.event_types', 'filter' => 'permission:event.event-types.read']);
    $routes->get('event-types/data', '\\App\\Modules\\Events\\Controllers\\EventTypeController::data', ['as' => 'admin.events.event_types.data', 'filter' => 'permission:event.event-types.read']);
    $routes->get('event-types/create', '\\App\\Modules\\Events\\Controllers\\EventTypeController::create', ['as' => 'admin.events.event_types.create', 'filter' => 'permission:event.event-types.write']);
    $routes->get('event-types/check-slug', '\\App\\Modules\\Events\\Controllers\\EventTypeController::checkSlug', ['as' => 'admin.events.event_types.check_slug', 'filter' => 'permission:event.event-types.read']);
    // Static action routes must precede the dynamic :id route below.
    $routes->get('event-types/reorder', '\\App\\Modules\\Events\\Controllers\\EventTypeController::reorder', ['as' => 'admin.events.event_types.reorder', 'filter' => 'permission:event.event-types.write']);
    $routes->post('event-types/reorder', '\\App\\Modules\\Events\\Controllers\\EventTypeController::saveOrder', ['as' => 'admin.events.event_types.save_order', 'filter' => 'permission:event.event-types.write']);
    $routes->post('event-types', '\\App\\Modules\\Events\\Controllers\\EventTypeController::store', ['as' => 'admin.events.event_types.store', 'filter' => 'permission:event.event-types.write']);
    $routes->get('event-types/(:segment)', '\\App\\Modules\\Events\\Controllers\\EventTypeController::show/$1', ['as' => 'admin.events.event_types.show', 'filter' => 'permission:event.event-types.read']);
    $routes->get('event-types/(:segment)/edit', '\\App\\Modules\\Events\\Controllers\\EventTypeController::edit/$1', ['as' => 'admin.events.event_types.edit', 'filter' => 'permission:event.event-types.write']);
    $routes->post('event-types/(:segment)', '\\App\\Modules\\Events\\Controllers\\EventTypeController::update/$1', ['as' => 'admin.events.event_types.update', 'filter' => 'permission:event.event-types.write']);
    $routes->post('event-types/(:segment)/delete', '\\App\\Modules\\Events\\Controllers\\EventTypeController::delete/$1', ['as' => 'admin.events.event_types.delete', 'filter' => 'permission:event.event-types.delete']);
});
