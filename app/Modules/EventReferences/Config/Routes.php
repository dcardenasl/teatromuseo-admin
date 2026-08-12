<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/eventreferences', ['filter' => ['auth']], static function (RouteCollection $routes): void {
    // EventReference
    $routes->get('event-references', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::index', ['as' => 'admin.eventreferences.event_references', 'filter' => 'permission:event.event-references.read']);
    $routes->get('event-references/data', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::data', ['as' => 'admin.eventreferences.event_references.data', 'filter' => 'permission:event.event-references.read']);
    $routes->get('event-references/create', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::create', ['as' => 'admin.eventreferences.event_references.create', 'filter' => 'permission:event.event-references.write']);
    $routes->post('event-references', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::store', ['as' => 'admin.eventreferences.event_references.store', 'filter' => 'permission:event.event-references.write']);
    $routes->get('event-references/(:segment)', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::show/$1', ['as' => 'admin.eventreferences.event_references.show', 'filter' => 'permission:event.event-references.read']);
    $routes->get('event-references/(:segment)/edit', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::edit/$1', ['as' => 'admin.eventreferences.event_references.edit', 'filter' => 'permission:event.event-references.write']);
    $routes->post('event-references/(:segment)', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::update/$1', ['as' => 'admin.eventreferences.event_references.update', 'filter' => 'permission:event.event-references.write']);
    $routes->post('event-references/(:segment)/delete', '\\App\\Modules\\EventReferences\\Controllers\\EventReferenceController::delete/$1', ['as' => 'admin.eventreferences.event_references.delete', 'filter' => 'permission:event.event-references.delete']);
});
