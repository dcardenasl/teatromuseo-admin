<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Dashboard\Services\DashboardDataService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class DashboardController extends BaseWebController
{
    protected DashboardDataService $dashboardDataService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->dashboardDataService = service('dashboardDataService');
    }

    public function index(): string
    {
        $user = is_array(session('user')) ? session('user') : [];
        $dashboard = $this->dashboardDataService->read($this->currentUserId(), $this->currentPermissions());
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

        $canViewAnalytics = has_permission('cms.analytics.read');
        $widgets = [
            'stats' => $this->renderStats($dashboard),
            'summary' => $this->renderSummary($dashboard),
            'translations' => $this->renderTranslations($dashboard),
            'activity' => $this->renderCmsActivity($dashboard),
            'analytics' => $this->renderAnalytics($dashboard),
            'health' => $this->renderSourceHealth($dashboard),
            'recentFiles' => $this->renderRecentFiles($dashboard),
        ];

        $this->closeSessionSafely();

        return $this->render('dashboard/index', [
            'title'       => lang('Dashboard.title'),
            'user'        => $user,
            'displayName' => $displayName,
            'widgets'     => $widgets,
            'canViewAnalytics' => $canViewAnalytics,
        ]);
    }

    /** @param array<string, mixed> $dashboard */
    private function renderStats(array $dashboard): string
    {
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

        return view('dashboard/partials/widget_stats', [
            'stats' => $stats,
            'sourceState' => $hubState,
        ]);
    }

    /** @param array<string, mixed> $dashboard */
    private function renderSourceHealth(array $dashboard): string
    {
        $labels = [
            'hub' => lang('Dashboard.service_hub'),
            'cms' => lang('Dashboard.source_cms'),
            'catalog' => lang('Dashboard.source_catalog'),
            'event' => lang('Dashboard.source_event'),
        ];
        $healthServices = [];
        foreach ($labels as $source => $label) {
            $state = $this->dashboardSourceState($dashboard, $source);
            $diagnostics = is_array($dashboard['diagnostics'] ?? null)
                ? $dashboard['diagnostics']
                : [];
            $diagnostic = is_array($diagnostics[$source] ?? null)
                ? $diagnostics[$source]
                : [];
            $latencyMs = is_numeric($diagnostic['latency_ms'] ?? null)
                ? round(max(0.0, (float) $diagnostic['latency_ms']), 2)
                : null;
            $checks = is_array($diagnostic['checks'] ?? null) ? $diagnostic['checks'] : [];
            $healthServices[] = [
                'name' => $label,
                'health' => [
                    'state' => $state === 'fresh' ? 'up' : ($state === 'stale' ? 'degraded' : 'down'),
                    'status' => $state === 'unavailable' ? 503 : 200,
                    'data' => [
                        'timestamp' => $dashboard['generated_at'] ?? null,
                        'checks' => $checks,
                    ],
                ] + ($latencyMs !== null ? ['latency_ms' => $latencyMs] : []),
            ];
        }

        $hosting = is_array($diagnostics['hosting'] ?? null) ? $diagnostics['hosting'] : [];
        $hostingChecks = is_array($hosting['checks'] ?? null) ? $hosting['checks'] : [];
        if ($hostingChecks !== []) {
            $healthServices[] = [
                'name' => lang('Dashboard.service_hosting'),
                'health' => [
                    'state' => $this->healthStateFromChecks($hostingChecks),
                    'status' => 200,
                    'data' => [
                        'timestamp' => $dashboard['generated_at'] ?? null,
                        'checks' => $hostingChecks,
                    ],
                ],
            ];
        }

        return view('dashboard/partials/widget_health', ['healthServices' => $healthServices]);
    }

    /** @param array<string, mixed> $checks */
    private function healthStateFromChecks(array $checks): string
    {
        foreach ($checks as $check) {
            if (is_array($check) && ($check['status'] ?? null) === 'unhealthy') {
                return 'down';
            }
        }

        foreach ($checks as $check) {
            if (is_array($check) && in_array($check['status'] ?? null, ['warning', 'critical', 'unknown'], true)) {
                return 'degraded';
            }
        }

        return 'up';
    }

    /** @param array<string, mixed> $dashboard */
    private function renderRecentFiles(array $dashboard): string
    {
        $hub       = $this->dashboardSection($dashboard, 'hub');
        $hubState  = $this->dashboardSourceState($dashboard, 'hub');

        return view('dashboard/partials/widget_recent_files', [
            'recentFiles' => is_array($hub['files']['recent'] ?? null) ? $hub['files']['recent'] : [],
            'sourceState' => $hubState,
        ]);
    }

    /**
     * Per-language translation completeness (mirrors the audit workbench's
     * stat cards) so the dashboard surfaces the project's actual translation
     * health instead of only generic ops metrics.
     */
    /** @param array<string, mixed> $dashboard */
    private function renderTranslations(array $dashboard): string
    {
        $section  = $this->dashboardSection($dashboard, 'translations');
        $state    = $this->dashboardSourceState($dashboard, 'translations');
        $stats    = $state === 'unavailable'
            ? null
            : (is_array($section['translations'] ?? null) ? $section['translations'] : []);

        return view('dashboard/partials/widget_translations', [
            'stats' => $stats,
        ]);
    }

    /**
     * Lightweight traffic snapshot (total views, unique visitors, top page,
     * top referrer) for the last 7 days, backed by the same cheap overview()
     * endpoint the full Analytics page uses for its KPI cards — never the
     * heavier per-page/referrer/timeseries breakdowns.
     */
    /** @param array<string, mixed> $dashboard */
    private function renderAnalytics(array $dashboard): string
    {
        $section  = $this->dashboardSection($dashboard, 'analytics');
        $state    = $this->dashboardSourceState($dashboard, 'analytics');
        $overview = $state === 'unavailable'
            ? null
            : (is_array($section['analytics'] ?? null) ? $section['analytics'] : []);

        return view('dashboard/partials/widget_analytics', [
            'overview' => $overview,
        ]);
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
    /** @param array<string, mixed> $dashboard */
    private function renderSummary(array $dashboard): string
    {
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

        return view('dashboard/partials/widget_summary', [
            'items' => $items,
            'warnings' => $warnings,
        ]);
    }

    /**
     * Most recently updated Pages and Entries, merged and sorted — the
     * project-relevant equivalent of a generic "recent activity" feed.
     */
    /** @param array<string, mixed> $dashboard */
    private function renderCmsActivity(array $dashboard): string
    {
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

        return view('dashboard/partials/widget_cms_activity', [
            'items' => array_slice($entries, 0, 6),
            'sourceStates' => [
                'cms' => $this->dashboardSourceState($dashboard, 'cms'),
                'catalog' => $this->dashboardSourceState($dashboard, 'catalog'),
                'event' => $this->dashboardSourceState($dashboard, 'event'),
            ],
        ]);
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
