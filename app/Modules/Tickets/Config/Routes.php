<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/tickets', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Ticket
    $routes->get('tickets', '\\App\\Modules\\Tickets\\Controllers\\TicketController::index', ['as' => 'admin.tickets.tickets']);
    $routes->get('tickets/data', '\\App\\Modules\\Tickets\\Controllers\\TicketController::data', ['as' => 'admin.tickets.tickets.data']);
    $routes->get('tickets/create', '\\App\\Modules\\Tickets\\Controllers\\TicketController::create', ['as' => 'admin.tickets.tickets.create']);
    $routes->post('tickets', '\\App\\Modules\\Tickets\\Controllers\\TicketController::store', ['as' => 'admin.tickets.tickets.store']);
    $routes->get('tickets/(:segment)', '\\App\\Modules\\Tickets\\Controllers\\TicketController::show/$1', ['as' => 'admin.tickets.tickets.show']);
    $routes->get('tickets/(:segment)/edit', '\\App\\Modules\\Tickets\\Controllers\\TicketController::edit/$1', ['as' => 'admin.tickets.tickets.edit']);
    $routes->post('tickets/(:segment)', '\\App\\Modules\\Tickets\\Controllers\\TicketController::update/$1', ['as' => 'admin.tickets.tickets.update']);
    $routes->post('tickets/(:segment)/delete', '\\App\\Modules\\Tickets\\Controllers\\TicketController::delete/$1', ['as' => 'admin.tickets.tickets.delete']);



});
