<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/files', '\App\Modules\Files\Controllers\FileController::index', ['as' => 'files', 'filter' => 'permission:files.read']);
    $routes->get('/files/data', '\App\Modules\Files\Controllers\FileController::data', ['as' => 'files.data', 'filter' => 'permission:files.read']);
    $routes->get('/files/trash', '\App\Modules\Files\Controllers\FileController::trash', ['as' => 'files.trash', 'filter' => 'permission:files.read']);
    $routes->get('/files/trash/data', '\App\Modules\Files\Controllers\FileController::trashData', ['as' => 'files.trash.data', 'filter' => 'permission:files.read']);
    $routes->post('/files/upload', '\App\Modules\Files\Controllers\FileController::upload', ['as' => 'files.upload', 'filter' => 'permission:files.write']);
    $routes->get('/files/picker-manifest', '\App\Modules\Files\Controllers\FileController::pickerManifest', ['as' => 'files.picker.manifest', 'filter' => 'permission:files.read']);
    $routes->get('/files/(:segment)/picker-info', '\App\Modules\Files\Controllers\FileController::pickerInfo/$1', ['as' => 'files.picker.info', 'filter' => 'permission:files.read']);
    $routes->get('/files/(:segment)/download', '\App\Modules\Files\Controllers\FileController::download/$1', ['as' => 'files.download', 'filter' => 'permission:files.read']);
    $routes->get('/files/(:segment)/view', '\App\Modules\Files\Controllers\FileController::view/$1', ['as' => 'files.view', 'filter' => 'permission:files.read']);
    $routes->get('/files/(:segment)/show', '\App\Modules\Files\Controllers\FileController::show/$1', ['as' => 'files.show', 'filter' => 'permission:files.read']);
    $routes->get('/files/(:segment)/usages', '\App\Modules\Files\Controllers\FileController::usagesJson/$1', ['as' => 'files.usages', 'filter' => 'permission:files.read']);
    $routes->post('/files/(:segment)/metadata', '\App\Modules\Files\Controllers\FileController::updateMeta/$1', ['as' => 'files.metadata', 'filter' => 'permission:files.write']);
    $routes->post('/files/(:segment)/restore', '\App\Modules\Files\Controllers\FileController::restore/$1', ['as' => 'files.restore', 'filter' => 'permission:files.write']);
    $routes->post('/files/(:segment)/force', '\App\Modules\Files\Controllers\FileController::forceDelete/$1', ['as' => 'files.force', 'filter' => 'permission:files.write']);
    $routes->post('/files/(:segment)/regenerate', '\App\Modules\Files\Controllers\FileController::regenerate/$1', ['as' => 'files.regenerate', 'filter' => 'permission:files.write']);
    $routes->post('/files/bulk', '\App\Modules\Files\Controllers\FileController::bulk', ['as' => 'files.bulk', 'filter' => 'permission:files.write']);
    $routes->post('/files/(:segment)/delete', '\App\Modules\Files\Controllers\FileController::delete/$1', ['as' => 'files.delete', 'filter' => 'permission:files.write']);
});
