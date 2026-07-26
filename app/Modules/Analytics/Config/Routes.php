<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin', ['filter' => ['auth', 'permission:cms.analytics.read']], static function (RouteCollection $routes): void {
    $routes->get('analytics', '\App\Modules\Analytics\Controllers\AnalyticsController::index', ['as' => 'admin.analytics']);
});
