<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/dashboard', '\App\Modules\Dashboard\Controllers\DashboardController::index', ['as' => 'dashboard']);

    // NOTE: 'sessionclose' (close the session lock before the controller runs,
    // so parallel widget AJAX calls don't queue behind each other) was tried
    // here and reverted same-day (2026-08-07). It's unsafe as a pre-controller
    // filter: widget controllers make authenticated ApiClient calls that can
    // 401 -> attemptTokenRefresh() -> (on failure) clearSessionAuth() ->
    // session->regenerate(true), which throws "Session ID cannot be
    // regenerated when there is no active session" if the session was already
    // closed. That crash is what broke login entirely. See TASKS.md FRONT-01h
    // for the real fix (close the session only after all ApiClient calls in
    // the request — including their error/refresh paths — are done).
    $routes->get('/dashboard/widgets/stats', '\App\Modules\Dashboard\Controllers\DashboardController::widgetStats', ['as' => 'dashboard.widgets.stats']);
    $routes->get('/dashboard/widgets/health', '\App\Modules\Dashboard\Controllers\DashboardController::widgetHealth', ['as' => 'dashboard.widgets.health']);
    $routes->get('/dashboard/widgets/recent-files', '\App\Modules\Dashboard\Controllers\DashboardController::widgetRecentFiles', ['as' => 'dashboard.widgets.recent-files']);
    $routes->get('/dashboard/widgets/translations', '\App\Modules\Dashboard\Controllers\DashboardController::widgetTranslations', ['as' => 'dashboard.widgets.translations']);
    $routes->get('/dashboard/widgets/summary', '\App\Modules\Dashboard\Controllers\DashboardController::widgetSummary', ['as' => 'dashboard.widgets.summary']);
    $routes->get('/dashboard/widgets/cms-activity', '\App\Modules\Dashboard\Controllers\DashboardController::widgetCmsActivity', ['as' => 'dashboard.widgets.cms-activity']);
    $routes->get('/dashboard/widgets/analytics', '\App\Modules\Dashboard\Controllers\DashboardController::widgetAnalytics', ['as' => 'dashboard.widgets.analytics']);
});
