<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/museum', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Category
    $routes->get('categories', '\\App\\Modules\\Museum\\Controllers\\CategoryController::index', ['as' => 'admin.museum.categories']);
    $routes->get('categories/data', '\\App\\Modules\\Museum\\Controllers\\CategoryController::data', ['as' => 'admin.museum.categories.data']);
    $routes->get('categories/create', '\\App\\Modules\\Museum\\Controllers\\CategoryController::create', ['as' => 'admin.museum.categories.create']);
    $routes->post('categories', '\\App\\Modules\\Museum\\Controllers\\CategoryController::store', ['as' => 'admin.museum.categories.store']);
    $routes->get('categories/(:segment)', '\\App\\Modules\\Museum\\Controllers\\CategoryController::show/$1', ['as' => 'admin.museum.categories.show']);
    $routes->get('categories/(:segment)/edit', '\\App\\Modules\\Museum\\Controllers\\CategoryController::edit/$1', ['as' => 'admin.museum.categories.edit']);
    $routes->post('categories/(:segment)', '\\App\\Modules\\Museum\\Controllers\\CategoryController::update/$1', ['as' => 'admin.museum.categories.update']);
    $routes->post('categories/(:segment)/delete', '\\App\\Modules\\Museum\\Controllers\\CategoryController::delete/$1', ['as' => 'admin.museum.categories.delete']);

    $routes->get('categories/reorder', '\\App\\Modules\\Museum\\Controllers\\CategoryController::reorder', ['as' => 'admin.museum.categories.reorder', 'filter' => 'permission:museum.write']);
    $routes->post('categories/reorder', '\\App\\Modules\\Museum\\Controllers\\CategoryController::saveOrder', ['as' => 'admin.museum.categories.save_order', 'filter' => 'permission:museum.write']);



    // Technique
    $routes->get('techniques', '\App\Modules\Museum\Controllers\TechniqueController::index', ['as' => 'admin.museum.techniques']);
    $routes->get('techniques/data', '\App\Modules\Museum\Controllers\TechniqueController::data', ['as' => 'admin.museum.techniques.data']);
    $routes->get('techniques/create', '\App\Modules\Museum\Controllers\TechniqueController::create', ['as' => 'admin.museum.techniques.create']);
    $routes->post('techniques', '\App\Modules\Museum\Controllers\TechniqueController::store', ['as' => 'admin.museum.techniques.store']);
    $routes->get('techniques/(:segment)', '\App\Modules\Museum\Controllers\TechniqueController::show/$1', ['as' => 'admin.museum.techniques.show']);
    $routes->get('techniques/(:segment)/edit', '\App\Modules\Museum\Controllers\TechniqueController::edit/$1', ['as' => 'admin.museum.techniques.edit']);
    $routes->post('techniques/(:segment)', '\App\Modules\Museum\Controllers\TechniqueController::update/$1', ['as' => 'admin.museum.techniques.update']);
    $routes->post('techniques/(:segment)/delete', '\App\Modules\Museum\Controllers\TechniqueController::delete/$1', ['as' => 'admin.museum.techniques.delete']);
    $routes->get('techniques/reorder', '\App\Modules\Museum\Controllers\TechniqueController::reorder', ['as' => 'admin.museum.techniques.reorder', 'filter' => 'permission:museum.write']);
    $routes->post('techniques/reorder', '\App\Modules\Museum\Controllers\TechniqueController::saveOrder', ['as' => 'admin.museum.techniques.save_order', 'filter' => 'permission:museum.write']);

    // CollectionItem
    $routes->get('collection-items', '\App\Modules\Museum\Controllers\CollectionItemController::index', ['as' => 'admin.museum.collection_items']);
    $routes->get('collection-items/data', '\App\Modules\Museum\Controllers\CollectionItemController::data', ['as' => 'admin.museum.collection_items.data']);
    $routes->get('collection-items/create', '\App\Modules\Museum\Controllers\CollectionItemController::create', ['as' => 'admin.museum.collection_items.create']);
    $routes->post('collection-items', '\App\Modules\Museum\Controllers\CollectionItemController::store', ['as' => 'admin.museum.collection_items.store']);
    $routes->get('collection-items/(:segment)', '\App\Modules\Museum\Controllers\CollectionItemController::show/$1', ['as' => 'admin.museum.collection_items.show']);
    $routes->get('collection-items/(:segment)/edit', '\App\Modules\Museum\Controllers\CollectionItemController::edit/$1', ['as' => 'admin.museum.collection_items.edit']);
    $routes->post('collection-items/(:segment)', '\App\Modules\Museum\Controllers\CollectionItemController::update/$1', ['as' => 'admin.museum.collection_items.update']);
    $routes->post('collection-items/(:segment)/delete', '\App\Modules\Museum\Controllers\CollectionItemController::delete/$1', ['as' => 'admin.museum.collection_items.delete']);
});
