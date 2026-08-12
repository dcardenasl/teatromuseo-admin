<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/tickettypes', ['filter' => ['auth']], static function (RouteCollection $routes): void {
    // TicketType
    $routes->get('ticket-types', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::index', ['as' => 'admin.tickettypes.ticket_types', 'filter' => 'permission:event.ticket-types.read']);
    $routes->get('ticket-types/data', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::data', ['as' => 'admin.tickettypes.ticket_types.data', 'filter' => 'permission:event.ticket-types.read']);
    $routes->get('ticket-types/create', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::create', ['as' => 'admin.tickettypes.ticket_types.create', 'filter' => 'permission:event.ticket-types.write']);
    $routes->post('ticket-types', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::store', ['as' => 'admin.tickettypes.ticket_types.store', 'filter' => 'permission:event.ticket-types.write']);
    $routes->get('ticket-types/(:segment)', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::show/$1', ['as' => 'admin.tickettypes.ticket_types.show', 'filter' => 'permission:event.ticket-types.read']);
    $routes->get('ticket-types/(:segment)/edit', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::edit/$1', ['as' => 'admin.tickettypes.ticket_types.edit', 'filter' => 'permission:event.ticket-types.write']);
    $routes->post('ticket-types/(:segment)', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::update/$1', ['as' => 'admin.tickettypes.ticket_types.update', 'filter' => 'permission:event.ticket-types.write']);
    $routes->post('ticket-types/(:segment)/delete', '\\App\\Modules\\TicketTypes\\Controllers\\TicketTypeController::delete/$1', ['as' => 'admin.tickettypes.ticket_types.delete', 'filter' => 'permission:event.ticket-types.delete']);
});
