<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/eventreferences', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // EventReference
    $routes->get('event-references', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::index', ['as' => 'admin.eventreferences.event_references']);
    $routes->get('event-references/data', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::data', ['as' => 'admin.eventreferences.event_references.data']);
    $routes->get('event-references/create', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::create', ['as' => 'admin.eventreferences.event_references.create']);
    $routes->post('event-references', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::store', ['as' => 'admin.eventreferences.event_references.store']);
    $routes->get('event-references/(:segment)', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::show/$1', ['as' => 'admin.eventreferences.event_references.show']);
    $routes->get('event-references/(:segment)/edit', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::edit/$1', ['as' => 'admin.eventreferences.event_references.edit']);
    $routes->post('event-references/(:segment)', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::update/$1', ['as' => 'admin.eventreferences.event_references.update']);
    $routes->post('event-references/(:segment)/delete', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::delete/$1', ['as' => 'admin.eventreferences.event_references.delete']);



});
