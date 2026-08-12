<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/museum', ['filter' => ['auth']], static function (RouteCollection $routes): void {
    // Category
    $routes->get('categories', '\\App\\Modules\\Museum\\Controllers\\CategoryController::index', ['as' => 'admin.museum.categories', 'filter' => 'permission:catalog.category.read']);
    $routes->get('categories/data', '\\App\\Modules\\Museum\\Controllers\\CategoryController::data', ['as' => 'admin.museum.categories.data', 'filter' => 'permission:catalog.category.read']);
    $routes->get('categories/create', '\\App\\Modules\\Museum\\Controllers\\CategoryController::create', ['as' => 'admin.museum.categories.create', 'filter' => 'permission:catalog.category.create']);
    $routes->post('categories', '\\App\\Modules\\Museum\\Controllers\\CategoryController::store', ['as' => 'admin.museum.categories.store', 'filter' => 'permission:catalog.category.create']);
    $routes->get('categories/(:segment)', '\\App\\Modules\\Museum\\Controllers\\CategoryController::show/$1', ['as' => 'admin.museum.categories.show', 'filter' => 'permission:catalog.category.read']);
    $routes->get('categories/(:segment)/edit', '\\App\\Modules\\Museum\\Controllers\\CategoryController::edit/$1', ['as' => 'admin.museum.categories.edit', 'filter' => 'permission:catalog.category.update']);
    $routes->post('categories/(:segment)', '\\App\\Modules\\Museum\\Controllers\\CategoryController::update/$1', ['as' => 'admin.museum.categories.update', 'filter' => 'permission:catalog.category.update']);
    $routes->post('categories/(:segment)/delete', '\\App\\Modules\\Museum\\Controllers\\CategoryController::delete/$1', ['as' => 'admin.museum.categories.delete', 'filter' => 'permission:catalog.category.delete']);

    $routes->get('categories/reorder', '\\App\\Modules\\Museum\\Controllers\\CategoryController::reorder', ['as' => 'admin.museum.categories.reorder', 'filter' => 'permission:catalog.category.update']);
    $routes->post('categories/reorder', '\\App\\Modules\\Museum\\Controllers\\CategoryController::saveOrder', ['as' => 'admin.museum.categories.save_order', 'filter' => 'permission:catalog.category.update']);



    // Technique
    $routes->get('techniques', '\App\Modules\Museum\Controllers\TechniqueController::index', ['as' => 'admin.museum.techniques', 'filter' => 'permission:catalog.technique.read']);
    $routes->get('techniques/data', '\App\Modules\Museum\Controllers\TechniqueController::data', ['as' => 'admin.museum.techniques.data', 'filter' => 'permission:catalog.technique.read']);
    $routes->get('techniques/create', '\App\Modules\Museum\Controllers\TechniqueController::create', ['as' => 'admin.museum.techniques.create', 'filter' => 'permission:catalog.technique.create']);
    $routes->post('techniques', '\App\Modules\Museum\Controllers\TechniqueController::store', ['as' => 'admin.museum.techniques.store', 'filter' => 'permission:catalog.technique.create']);
    $routes->get('techniques/(:segment)', '\App\Modules\Museum\Controllers\TechniqueController::show/$1', ['as' => 'admin.museum.techniques.show', 'filter' => 'permission:catalog.technique.read']);
    $routes->get('techniques/(:segment)/edit', '\App\Modules\Museum\Controllers\TechniqueController::edit/$1', ['as' => 'admin.museum.techniques.edit', 'filter' => 'permission:catalog.technique.update']);
    $routes->post('techniques/(:segment)', '\App\Modules\Museum\Controllers\TechniqueController::update/$1', ['as' => 'admin.museum.techniques.update', 'filter' => 'permission:catalog.technique.update']);
    $routes->post('techniques/(:segment)/delete', '\App\Modules\Museum\Controllers\TechniqueController::delete/$1', ['as' => 'admin.museum.techniques.delete', 'filter' => 'permission:catalog.technique.delete']);
    $routes->get('techniques/reorder', '\App\Modules\Museum\Controllers\TechniqueController::reorder', ['as' => 'admin.museum.techniques.reorder', 'filter' => 'permission:catalog.technique.update']);
    $routes->post('techniques/reorder', '\App\Modules\Museum\Controllers\TechniqueController::saveOrder', ['as' => 'admin.museum.techniques.save_order', 'filter' => 'permission:catalog.technique.update']);

    // CollectionItem
    $routes->get('collection-items', '\App\Modules\Museum\Controllers\CollectionItemController::index', ['as' => 'admin.museum.collection_items', 'filter' => 'permission:catalog.collectionItem.read']);
    $routes->get('collection-items/data', '\App\Modules\Museum\Controllers\CollectionItemController::data', ['as' => 'admin.museum.collection_items.data', 'filter' => 'permission:catalog.collectionItem.read']);
    $routes->get('collection-items/create', '\App\Modules\Museum\Controllers\CollectionItemController::create', ['as' => 'admin.museum.collection_items.create', 'filter' => 'permission:catalog.collectionItem.create']);
    $routes->post('collection-items', '\App\Modules\Museum\Controllers\CollectionItemController::store', ['as' => 'admin.museum.collection_items.store', 'filter' => 'permission:catalog.collectionItem.create']);
    $routes->get('collection-items/(:segment)', '\App\Modules\Museum\Controllers\CollectionItemController::show/$1', ['as' => 'admin.museum.collection_items.show', 'filter' => 'permission:catalog.collectionItem.read']);
    $routes->get('collection-items/(:segment)/edit', '\App\Modules\Museum\Controllers\CollectionItemController::edit/$1', ['as' => 'admin.museum.collection_items.edit', 'filter' => 'permission:catalog.collectionItem.update']);
    $routes->post('collection-items/(:segment)', '\App\Modules\Museum\Controllers\CollectionItemController::update/$1', ['as' => 'admin.museum.collection_items.update', 'filter' => 'permission:catalog.collectionItem.update']);
    $routes->post('collection-items/(:segment)/delete', '\App\Modules\Museum\Controllers\CollectionItemController::delete/$1', ['as' => 'admin.museum.collection_items.delete', 'filter' => 'permission:catalog.collectionItem.delete']);
});
