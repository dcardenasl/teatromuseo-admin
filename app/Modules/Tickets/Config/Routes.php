<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/tickets', ['filter' => ['auth']], static function (RouteCollection $routes): void {
    // Ticket
    $routes->get('tickets', '\\App\\Modules\\Tickets\\Controllers\\TicketController::index', ['as' => 'admin.tickets.tickets', 'filter' => 'permission:event.tickets.read']);
    $routes->get('tickets/data', '\\App\\Modules\\Tickets\\Controllers\\TicketController::data', ['as' => 'admin.tickets.tickets.data', 'filter' => 'permission:event.tickets.read']);
    $routes->get('tickets/create', '\\App\\Modules\\Tickets\\Controllers\\TicketController::create', ['as' => 'admin.tickets.tickets.create', 'filter' => 'permission:event.tickets.write']);
    $routes->post('tickets', '\\App\\Modules\\Tickets\\Controllers\\TicketController::store', ['as' => 'admin.tickets.tickets.store', 'filter' => 'permission:event.tickets.write']);
    $routes->get('tickets/(:segment)', '\\App\\Modules\\Tickets\\Controllers\\TicketController::show/$1', ['as' => 'admin.tickets.tickets.show', 'filter' => 'permission:event.tickets.read']);
    $routes->get('tickets/(:segment)/edit', '\\App\\Modules\\Tickets\\Controllers\\TicketController::edit/$1', ['as' => 'admin.tickets.tickets.edit', 'filter' => 'permission:event.tickets.write']);
    $routes->post('tickets/(:segment)', '\\App\\Modules\\Tickets\\Controllers\\TicketController::update/$1', ['as' => 'admin.tickets.tickets.update', 'filter' => 'permission:event.tickets.write']);
    $routes->post('tickets/(:segment)/delete', '\\App\\Modules\\Tickets\\Controllers\\TicketController::delete/$1', ['as' => 'admin.tickets.tickets.delete', 'filter' => 'permission:event.tickets.delete']);
});
