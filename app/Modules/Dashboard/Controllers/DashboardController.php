<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Analytics\Services\AnalyticsApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Dashboard\Services\DashboardDataService;
use App\Modules\Dashboard\Services\HealthApiService;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class DashboardController extends BaseWebController
{
    protected DashboardDataService $dashboardDataService;
    protected HealthApiService $healthService;
    protected TranslationAuditApiService $translationAuditService;
    protected AnalyticsApiService $analyticsService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->dashboardDataService     = service('dashboardDataService');
        $this->healthService           = service('healthApiService');
        $this->translationAuditService = service('translationAuditApiService');
        $this->analyticsService         = service('analyticsApiService');
    }

    public function index(): string
    {
        $user = is_array(session('user')) ? session('user') : [];
        $displayName = trim((string) ($user['first_name'] ?? ''));
        if ($displayName === '') {
            $displayName = trim((string) ($user['username'] ?? ''));
        }
        if ($displayName === '') {
            $displayName = trim((string) ($user['email'] ?? ''));
        }
        if ($displayName === '') {
            $displayName = lang('Dashboard.user_fallback');
        }

        return $this->render('dashboard/index', [
            'title'       => lang('Dashboard.title'),
            'user'        => $user,
            'displayName' => $displayName,
        ]);
    }

    public function widgetStats(): ResponseInterface
    {
        $dashboard = $this->dashboardDataService->read($this->currentUserId(), $this->currentPermissions());
        $hub       = $this->dashboardSection($dashboard, 'hub');
        $hubState  = $this->dashboardSourceState($dashboard, 'hub');
        $metrics   = is_array($hub['metrics'] ?? null) ? $hub['metrics'] : [];
        $uptime    = $metrics['request_stats']['availability_percent']
            ?? $metrics['slo']['availability_percent']
            ?? null;
        $isAdmin   = has_permission('users.read');

        $stats = [];
        if ($hubState !== 'unavailable') {
            $stats = [
                'users' => ['label' => lang('Dashboard.total_users'), 'value' => $isAdmin ? (int) ($hub['users']['total'] ?? 0) : 0, 'icon' => 'users'],
                'files' => ['label' => lang('Dashboard.total_files'), 'value' => (int) ($hub['files']['total'] ?? 0), 'icon' => 'files'],
            ];
        }
        if ($uptime !== null) {
            $stats['uptime'] = ['label' => lang('Dashboard.api_uptime'), 'value' => $uptime . '%', 'icon' => 'activity'];
        }

        $this->closeSessionSafely();

        return $this->response->setBody(view('dashboard/partials/widget_stats', [
            'stats' => $stats,
            'sourceState' => $hubState,
        ]));
    }

    public function widgetHealth(): ResponseInterface
    {
        $cache = service('cache');

        $hubHealth = $this->fetchCachedHealth('dashboard_health_hub', $this->healthService, $cache);

        $domainUrl    = config('DomainApiClient')->baseUrl;
        $domainHealth = ($domainUrl !== '')
            ? $this->fetchCachedHealth('dashboard_health_domain', service('domainHealthApiService'), $cache)
            : null;

        $bffUrl    = config('BffApiClient')->baseUrl;
        $bffHealth = ($bffUrl !== '')
            ? $this->fetchCachedHealth('dashboard_health_bff', service('bffHealthApiService'), $cache)
            : null;

        $webUrl    = config('WebApiClient')->baseUrl;
        $webHealth = ($webUrl !== '')
            ? $this->fetchCachedHealth('dashboard_health_web', service('webHealthApiService'), $cache)
            : null;

        $healthServices = [
            ['name' => lang('Dashboard.service_hub'), 'health' => $hubHealth],
        ];
        if ($domainHealth !== null) {
            $healthServices[] = ['name' => lang('Dashboard.service_domain'), 'health' => $domainHealth];
        }
        if ($bffHealth !== null) {
            $healthServices[] = ['name' => lang('Dashboard.service_bff'), 'health' => $bffHealth];
        }
        if ($webHealth !== null) {
            $healthServices[] = ['name' => lang('Dashboard.service_web'), 'health' => $webHealth];
        }

        $devPanel = $this->renderDevApiErrorPanel($hubHealth)
            . ($domainHealth !== null ? $this->renderDevApiErrorPanel($domainHealth) : '')
            . ($bffHealth !== null ? $this->renderDevApiErrorPanel($bffHealth) : '')
            . ($webHealth !== null ? $this->renderDevApiErrorPanel($webHealth) : '');

        $this->closeSessionSafely();

        return $this->response->setBody($devPanel . view('dashboard/partials/widget_health', [
            'healthServices' => $healthServices,
        ]));
    }

    public function widgetRecentFiles(): ResponseInterface
    {
        $dashboard = $this->dashboardDataService->read($this->currentUserId(), $this->currentPermissions());
        $hub       = $this->dashboardSection($dashboard, 'hub');
        $hubState  = $this->dashboardSourceState($dashboard, 'hub');

        $this->closeSessionSafely();

        return $this->response->setBody(view('dashboard/partials/widget_recent_files', [
            'recentFiles' => is_array($hub['files']['recent'] ?? null) ? $hub['files']['recent'] : [],
            'sourceState' => $hubState,
        ]));
    }

    /**
     * Per-language translation completeness (mirrors the audit workbench's
     * stat cards) so the dashboard surfaces the project's actual translation
     * health instead of only generic ops metrics.
     */
    public function widgetTranslations(): ResponseInterface
    {
        if (! has_permission('cms.languages.read')) {
            $this->closeSessionSafely();

            return $this->response->setBody(view('dashboard/partials/widget_translations', ['stats' => null]));
        }

        $cache    = service('cache');
        $response = $cache->get('dashboard_translation_stats');
        if (!is_array($response)) {
            $response = $this->safeApiCall(fn () => $this->translationAuditService->getStats());
            if ($response['ok'] ?? false) {
                $cache->save('dashboard_translation_stats', $response, 300);
            }
        }

        $this->closeSessionSafely();

        return $this->response->setBody($this->renderDevApiErrorPanel($response) . view('dashboard/partials/widget_translations', [
            'stats' => $this->extractItems($response),
        ]));
    }

    /**
     * Lightweight traffic snapshot (total views, unique visitors, top page,
     * top referrer) for the last 7 days, backed by the same cheap overview()
     * endpoint the full Analytics page uses for its KPI cards — never the
     * heavier per-page/referrer/timeseries breakdowns.
     */
    public function widgetAnalytics(): ResponseInterface
    {
        if (! has_permission('cms.analytics.read')) {
            $this->closeSessionSafely();

            return $this->response->setBody(view('dashboard/partials/widget_analytics', ['overview' => null]));
        }

        $cache    = service('cache');
        $response = $cache->get('dashboard_analytics_overview');
        if (!is_array($response)) {
            $response = $this->safeApiCall(fn () => $this->analyticsService->overview(['period' => '7d']));
            if ($response['ok'] ?? false) {
                $cache->save('dashboard_analytics_overview', $response, 300);
            }
        }

        $this->closeSessionSafely();

        return $this->response->setBody($this->renderDevApiErrorPanel($response) . view('dashboard/partials/widget_analytics', [
            'overview' => $this->extractData($response),
        ]));
    }

    /**
     * Full permission-aware overview: a count per CMS resource type plus
     * form submissions (total + a "pending review" badge), one glance at
     * "what's going on in my site right now". Each entry — and the
     * submissions badge — is gated by its own read permission and omitted
     * entirely when denied, so editors and admins each see only what they
     * can reach. This subsumes what used to be a separate "needs attention"
     * widget: translation gaps already have their own dedicated widget with
     * per-language detail, so surfacing a duplicate "pending translations"
     * counter here added no information: it was removed in favor of the one
     * real actionable signal (submissions) living directly on its card.
     */
    public function widgetSummary(): ResponseInterface
    {
        $dashboard = $this->dashboardDataService->read($this->currentUserId(), $this->currentPermissions());
        $cms       = $this->dashboardSection($dashboard, 'cms');
        $catalog    = $this->dashboardSection($dashboard, 'catalog');
        $event      = $this->dashboardSection($dashboard, 'event');
        $cmsState   = $this->dashboardSourceState($dashboard, 'cms');
        $catalogState = $this->dashboardSourceState($dashboard, 'catalog');
        $eventState = $this->dashboardSourceState($dashboard, 'event');
        $counts    = is_array($cms['counts'] ?? null) ? $cms['counts'] : [];
        $catalogCounts = is_array($catalog['counts'] ?? null) ? $catalog['counts'] : [];
        $eventCounts = is_array($event['counts'] ?? null) ? $event['counts'] : [];
        $items = [];
        $warnings = [];

        foreach (['cms' => $cmsState, 'catalog' => $catalogState, 'event' => $eventState] as $source => $state) {
            if ($state === 'unavailable') {
                $warnings[] = lang('Dashboard.source_unavailable_named', [
                    'source' => lang('Dashboard.source_' . $source),
                ]);
            }
        }

        /** @var list<array{source: string, permission: string, countKey: string, label: string, url: string, icon: string}> $resources */
        $resources = [
            [
                'source' => 'cms',
                'permission' => 'cms.pages.read',
                'countKey'   => 'pages',
                'label'      => lang('Pages.pages_title'),
                'url'        => route_to('admin.cms.pages'),
                'icon'       => 'cms-page',
            ],
            [
                'source' => 'cms',
                'permission' => 'cms.entries.read',
                'countKey'   => 'entries',
                'label'      => lang('Entries.entries_title'),
                'url'        => route_to('admin.cms.entries'),
                'icon'       => 'cms-entry',
            ],
            [
                'source' => 'cms',
                'permission' => 'cms.collections.read',
                'countKey'   => 'collections',
                'label'      => lang('Collections.collections_title'),
                'url'        => route_to('admin.cms.collections'),
                'icon'       => 'cms-collection',
            ],
            [
                'source' => 'cms',
                'permission' => 'cms.menus.read',
                'countKey'   => 'menus',
                'label'      => lang('Menus.menus_title'),
                'url'        => route_to('admin.cms.menus'),
                'icon'       => 'cms-menu',
            ],
            [
                'source' => 'cms',
                'permission' => 'cms.categories.read',
                'countKey'   => 'categories',
                'label'      => lang('Categories.categories_title'),
                'url'        => route_to('admin.cms.categories'),
                'icon'       => 'folder-open',
            ],
            [
                'source' => 'cms',
                'permission' => 'cms.tags.read',
                'countKey'   => 'tags',
                'label'      => lang('Tags.tags_title'),
                'url'        => route_to('admin.cms.tags'),
                'icon'       => 'tag',
            ],
            [
                'source' => 'catalog',
                'permission' => 'catalog.collectionItem.read',
                'countKey' => 'collection_items',
                'label' => lang('Museum.collection_items_title'),
                'url' => route_to('admin.museum.collection_items'),
                'icon' => 'landmark',
            ],
            [
                'source' => 'catalog',
                'permission' => 'catalog.category.read',
                'countKey' => 'categories',
                'label' => lang('Museum.categories_title'),
                'url' => route_to('admin.museum.categories'),
                'icon' => 'folder-tree',
            ],
            [
                'source' => 'catalog',
                'permission' => 'catalog.technique.read',
                'countKey' => 'techniques',
                'label' => lang('Museum.techniques_title'),
                'url' => route_to('admin.museum.techniques'),
                'icon' => 'brush',
            ],
            [
                'source' => 'event',
                'permission' => 'event.events.read',
                'countKey' => 'events',
                'label' => lang('Events.events_title'),
                'url' => route_to('admin.events.events'),
                'icon' => 'calendar-days',
            ],
            [
                'source' => 'event',
                'permission' => 'event.event-types.read',
                'countKey' => 'event_types',
                'label' => lang('Events.event_types_title'),
                'url' => route_to('admin.events.event_types'),
                'icon' => 'layers-3',
            ],
            [
                'source' => 'event',
                'permission' => 'event.venues.read',
                'countKey' => 'venues',
                'label' => lang('Venues.venues_title'),
                'url' => route_to('admin.venues.venues'),
                'icon' => 'map-pin',
            ],
            [
                'source' => 'event',
                'permission' => 'event.occurrences.read',
                'countKey' => 'occurrences',
                'label' => lang('Occurrences.occurrences_title'),
                'url' => route_to('admin.occurrences.occurrences'),
                'icon' => 'clock-3',
            ],
            [
                'source' => 'event',
                'permission' => 'event.ticket-types.read',
                'countKey' => 'ticket_types',
                'label' => lang('TicketTypes.ticket_types_title'),
                'url' => route_to('admin.tickettypes.ticket_types'),
                'icon' => 'ticket',
            ],
            [
                'source' => 'event',
                'permission' => 'event.bookings.read',
                'countKey' => 'bookings',
                'label' => lang('Bookings.bookings_title'),
                'url' => route_to('admin.bookings.bookings'),
                'icon' => 'clipboard-check',
            ],
            [
                'source' => 'event',
                'permission' => 'event.tickets.read',
                'countKey' => 'tickets',
                'label' => lang('Tickets.tickets_title'),
                'url' => route_to('admin.tickets.tickets'),
                'icon' => 'badge-check',
            ],
        ];

        foreach ($resources as $resource) {
            if (! has_permission($resource['permission'])) {
                continue;
            }

            if ($this->dashboardSourceState($dashboard, $resource['source']) === 'unavailable') {
                continue;
            }

            $sourceCounts = match ($resource['source']) {
                'catalog' => $catalogCounts,
                'event' => $eventCounts,
                default => $counts,
            };

            $items[] = [
                'label' => $resource['label'],
                'count' => (int) ($sourceCounts[$resource['countKey']] ?? 0),
                'url'   => $resource['url'],
                'icon'  => $resource['icon'],
                'badge' => null,
            ];
        }

        if (has_permission('cms.forms.read') && $cmsState !== 'unavailable') {
            $items[] = [
                'label' => lang('Forms.title'),
                'count' => (int) ($counts['forms'] ?? 0),
                'url'   => route_to('admin.cms.forms'),
                'icon'  => 'clipboard-list',
                'badge' => null,
            ];
        }

        if (has_permission('cms.submissions.read') && $cmsState !== 'unavailable') {
            $submissionCounts = is_array($cms['submissions'] ?? null) ? $cms['submissions'] : [];
            $pending = (int) ($submissionCounts['new'] ?? 0);
            $total   = array_sum(array_map(static fn ($value): int => (int) $value, $submissionCounts));

            $items[] = [
                'label' => lang('FormSubmissions.submissions_title'),
                'count' => $total,
                'url'   => route_to('admin.cms.form_submissions'),
                'icon'  => 'mail',
                'badge' => $pending > 0 ? [
                    'count' => $pending,
                    'label' => lang('Dashboard.pending_review'),
                    'url'   => route_to('admin.cms.form_submissions') . '?status=new',
                ] : null,
            ];
        }

        $this->closeSessionSafely();

        return $this->response->setBody(view('dashboard/partials/widget_summary', [
            'items' => $items,
            'warnings' => $warnings,
        ]));
    }

    /**
     * Most recently updated Pages and Entries, merged and sorted — the
     * project-relevant equivalent of a generic "recent activity" feed.
     */
    public function widgetCmsActivity(): ResponseInterface
    {
        $dashboard = $this->dashboardDataService->read($this->currentUserId(), $this->currentPermissions());
        $cms       = $this->dashboardSection($dashboard, 'cms');
        $catalog   = $this->dashboardSection($dashboard, 'catalog');
        $event     = $this->dashboardSection($dashboard, 'event');
        $entries   = [];
        $activity  = is_array($cms['recent_activity'] ?? null) ? $cms['recent_activity'] : [];

        foreach ($activity as $activityItem) {
            if (! is_array($activityItem)) {
                continue;
            }

            $type = (string) ($activityItem['type'] ?? '');
            $isPage = $type === 'page';
            $entries[] = $this->buildActivityEntry(
                $activityItem,
                $isPage ? lang('Translations.resource_page') : lang('Translations.resource_entry'),
                $isPage
                    ? route_to('admin.cms.pages.show', (string) ($activityItem['id'] ?? ''))
                    : route_to('admin.cms.entries.show', (string) ($activityItem['id'] ?? ''))
            );
        }

        $domainActivity = [
            'catalog' => is_array($catalog['recent_activity'] ?? null) ? $catalog['recent_activity'] : [],
            'event' => is_array($event['recent_activity'] ?? null) ? $event['recent_activity'] : [],
        ];
        $domainActivityMap = [
            'collection_items' => [lang('Museum.collection_items_title'), 'admin.museum.collection_items.show'],
            'categories' => [lang('Museum.categories_title'), 'admin.museum.categories.show'],
            'techniques' => [lang('Museum.techniques_title'), 'admin.museum.techniques.show'],
            'events' => [lang('Events.events_title'), 'admin.events.events.show'],
            'event_types' => [lang('Events.event_types_title'), 'admin.events.event_types.show'],
            'venues' => [lang('Venues.venues_title'), 'admin.venues.venues.show'],
            'occurrences' => [lang('Occurrences.occurrences_title'), 'admin.occurrences.occurrences.show'],
            'ticket_types' => [lang('TicketTypes.ticket_types_title'), 'admin.tickettypes.ticket_types.show'],
            'bookings' => [lang('Bookings.bookings_title'), 'admin.bookings.bookings.show'],
            'tickets' => [lang('Tickets.tickets_title'), 'admin.tickets.tickets.show'],
        ];
        foreach ($domainActivity as $sourceItems) {
            foreach ($sourceItems as $activityItem) {
                if (! is_array($activityItem)) {
                    continue;
                }
                $type = (string) ($activityItem['type'] ?? '');
                if (! isset($domainActivityMap[$type])) {
                    continue;
                }
                [$label, $route] = $domainActivityMap[$type];
                $entries[] = $this->buildActivityEntry(
                    $activityItem,
                    $label,
                    route_to($route, (string) ($activityItem['id'] ?? ''))
                );
            }
        }

        usort($entries, static fn (array $a, array $b): int => strcmp((string) $b['updated_at'], (string) $a['updated_at']));

        $this->closeSessionSafely();

        return $this->response->setBody(view('dashboard/partials/widget_cms_activity', [
            'items' => array_slice($entries, 0, 6),
            'sourceStates' => [
                'cms' => $this->dashboardSourceState($dashboard, 'cms'),
                'catalog' => $this->dashboardSourceState($dashboard, 'catalog'),
                'event' => $this->dashboardSourceState($dashboard, 'event'),
            ],
        ]));
    }

    /**
     * @param array<string, mixed> $resource
     * @return array{title: string, type_label: string, url: string, updated_at: string}
     */
    private function buildActivityEntry(array $resource, string $typeLabel, string $url): array
    {
        $translations = is_array($resource['translations'] ?? null) ? $resource['translations'] : [];
        $firstTranslation = is_array($translations[0] ?? null) ? $translations[0] : [];
        $title = trim((string) ($firstTranslation['title'] ?? $firstTranslation['name'] ?? ''));
        if ($title === '') {
            $title = trim((string) ($resource['slug'] ?? '')) !== '' ? (string) $resource['slug'] : '#' . (string) ($resource['id'] ?? '');
        }

        return [
            'title'      => $title,
            'type_label' => $typeLabel,
            'url'        => $url,
            'updated_at' => (string) ($resource['updated_at'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCachedHealth(string $cacheKey, HealthApiService $service, CacheInterface $cache): array
    {
        $cached = $cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $response = $this->safeApiCall(fn () => $service->check());
        if ($response['ok'] ?? false) {
            $cache->save($cacheKey, $response, 30);
        }

        return $response;
    }

    private function currentUserId(): int
    {
        return (int) ((session('user') ?? [])['id'] ?? 0);
    }

    /** @return list<string> */
    private function currentPermissions(): array
    {
        $permissions = (session('user') ?? [])['permissions'] ?? [];

        if (! is_array($permissions)) {
            return [];
        }

        return array_values(array_filter($permissions, 'is_string'));
    }

    /**
     * @param array<string, mixed> $dashboard
     * @return array<string, mixed>
     */
    private function dashboardSection(array $dashboard, string $name): array
    {
        $sections = $dashboard['sections'] ?? [];
        $section = is_array($sections) ? ($sections[$name] ?? []) : [];

        return is_array($section) ? $section : [];
    }

    /**
     * @param array<string, mixed> $dashboard
     */
    private function dashboardSourceState(array $dashboard, string $name): string
    {
        $source = $dashboard['source'] ?? [];
        if (! is_array($source)) {
            return 'unavailable';
        }

        $state = $source[$name] ?? null;

        return is_string($state) && in_array($state, ['fresh', 'stale', 'unavailable'], true)
            ? $state
            : 'unavailable';
    }

    private function closeSessionSafely(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session()->close();
        }
    }
}
