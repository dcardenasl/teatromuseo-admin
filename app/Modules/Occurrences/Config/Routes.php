<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/occurrences', ['filter' => ['auth']], static function (RouteCollection $routes): void {
    // Occurrence
    $routes->get('occurrences', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::index', ['as' => 'admin.occurrences.occurrences', 'filter' => 'permission:event.occurrences.read']);
    $routes->get('occurrences/data', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::data', ['as' => 'admin.occurrences.occurrences.data', 'filter' => 'permission:event.occurrences.read']);
    $routes->get('occurrences/create', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::create', ['as' => 'admin.occurrences.occurrences.create', 'filter' => 'permission:event.occurrences.write']);
    $routes->post('occurrences', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::store', ['as' => 'admin.occurrences.occurrences.store', 'filter' => 'permission:event.occurrences.write']);
    $routes->get('occurrences/(:segment)', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::show/$1', ['as' => 'admin.occurrences.occurrences.show', 'filter' => 'permission:event.occurrences.read']);
    $routes->get('occurrences/(:segment)/edit', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::edit/$1', ['as' => 'admin.occurrences.occurrences.edit', 'filter' => 'permission:event.occurrences.write']);
    $routes->post('occurrences/(:segment)', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::update/$1', ['as' => 'admin.occurrences.occurrences.update', 'filter' => 'permission:event.occurrences.write']);
    $routes->post('occurrences/(:segment)/delete', '\\App\\Modules\\Occurrences\\Controllers\\OccurrenceController::delete/$1', ['as' => 'admin.occurrences.occurrences.delete', 'filter' => 'permission:event.occurrences.delete']);
});
