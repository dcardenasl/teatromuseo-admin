<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/dashboard', '\App\Modules\Dashboard\Controllers\DashboardController::index', ['as' => 'dashboard']);

    // The widget endpoints are read-only after AuthFilter. Release the PHP
    // session lock before any upstream I/O so multiple tabs/workers cannot
    // queue behind one slow Hub/CMS request. SessionCloseFilter keeps the
    // session open only when a token is within the refresh window.
    $widgetFilters = ['filter' => 'sessionclose'];
    $routes->get('/dashboard/widgets/stats', '\App\Modules\Dashboard\Controllers\DashboardController::widgetStats', ['as' => 'dashboard.widgets.stats', ...$widgetFilters]);
    $routes->get('/dashboard/widgets/health', '\App\Modules\Dashboard\Controllers\DashboardController::widgetHealth', ['as' => 'dashboard.widgets.health', ...$widgetFilters]);
    $routes->get('/dashboard/widgets/recent-files', '\App\Modules\Dashboard\Controllers\DashboardController::widgetRecentFiles', ['as' => 'dashboard.widgets.recent-files', ...$widgetFilters]);
    $routes->get('/dashboard/widgets/translations', '\App\Modules\Dashboard\Controllers\DashboardController::widgetTranslations', ['as' => 'dashboard.widgets.translations', ...$widgetFilters]);
    $routes->get('/dashboard/widgets/summary', '\App\Modules\Dashboard\Controllers\DashboardController::widgetSummary', ['as' => 'dashboard.widgets.summary', ...$widgetFilters]);
    $routes->get('/dashboard/widgets/cms-activity', '\App\Modules\Dashboard\Controllers\DashboardController::widgetCmsActivity', ['as' => 'dashboard.widgets.cms-activity', ...$widgetFilters]);
    $routes->get('/dashboard/widgets/analytics', '\App\Modules\Dashboard\Controllers\DashboardController::widgetAnalytics', ['as' => 'dashboard.widgets.analytics', ...$widgetFilters]);
});
