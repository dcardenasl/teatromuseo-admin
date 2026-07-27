<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/occurrences', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Occurrence
    $routes->get('occurrences', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::index', ['as' => 'admin.occurrences.occurrences']);
    $routes->get('occurrences/data', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::data', ['as' => 'admin.occurrences.occurrences.data']);
    $routes->get('occurrences/create', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::create', ['as' => 'admin.occurrences.occurrences.create']);
    $routes->post('occurrences', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::store', ['as' => 'admin.occurrences.occurrences.store']);
    $routes->get('occurrences/(:segment)', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::show/$1', ['as' => 'admin.occurrences.occurrences.show']);
    $routes->get('occurrences/(:segment)/edit', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::edit/$1', ['as' => 'admin.occurrences.occurrences.edit']);
    $routes->post('occurrences/(:segment)', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::update/$1', ['as' => 'admin.occurrences.occurrences.update']);
    $routes->post('occurrences/(:segment)/delete', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::delete/$1', ['as' => 'admin.occurrences.occurrences.delete']);



});
