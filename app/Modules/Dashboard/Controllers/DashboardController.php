<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Analytics\Services\AnalyticsApiService;
use App\Modules\Cms\Services\CategoryApiService;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\FormSubmissionApiService;
use App\Modules\Cms\Services\MenuApiService;
use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Services\TagApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Dashboard\Services\HealthApiService;
use App\Modules\Files\Services\FileApiService;
use App\Modules\Metrics\Services\MetricsApiService;
use App\Modules\Users\Services\UserApiService;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class DashboardController extends BaseWebController
{
    protected FileApiService $fileService;
    protected HealthApiService $healthService;
    protected MetricsApiService $metricsService;
    protected UserApiService $userService;
    protected TranslationAuditApiService $translationAuditService;
    protected FormSubmissionApiService $formSubmissionService;
    protected PageApiService $pageService;
    protected EntryApiService $entryService;
    protected CollectionApiService $collectionService;
    protected MenuApiService $menuService;
    protected CategoryApiService $categoryService;
    protected TagApiService $tagService;
    protected FormApiService $formService;
    protected AnalyticsApiService $analyticsService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->fileService             = service('fileApiService');
        $this->healthService           = service('healthApiService');
        $this->metricsService          = service('metricsApiService');
        $this->userService             = service('userApiService');
        $this->translationAuditService = service('translationAuditApiService');
        $this->formSubmissionService   = service('formSubmissionApiService');
        $this->pageService             = service('pageApiService');
        $this->entryService            = service('entryApiService');
        $this->collectionService       = service('collectionApiService');
        $this->menuService             = service('menuApiService');
        $this->categoryService         = service('categoryApiService');
        $this->tagService               = service('tagApiService');
        $this->formService             = service('formApiService');
        $this->analyticsService         = service('analyticsApiService');
    }

    public function index(): string
    {
        return $this->render('dashboard/index', [
            'title' => lang('Dashboard.title'),
            'user'  => session('user') ?? [],
        ]);
    }

    public function widgetStats(): ResponseInterface
    {
        $isAdmin   = has_permission('users.read');
        $userId    = (int) ((session('user') ?? [])['id'] ?? 0);
        $cache     = service('cache');
        $dateRange = $this->resolveDateRange();

        $metricsCacheKey = 'dashboard_metrics_' . md5(serialize($dateRange));
        $metricsResponse = $cache->get($metricsCacheKey);
        if (!is_array($metricsResponse)) {
            $metricsResponse = $this->safeApiCall(fn () => $this->metricsService->summary($dateRange));
            if ($metricsResponse['ok'] ?? false) {
                $cache->save($metricsCacheKey, $metricsResponse, 120);
            }
        }
        $metrics = $this->extractData($metricsResponse);

        $filesCacheKey = 'dashboard_files_' . $userId;
        $filesResponse = $cache->get($filesCacheKey);
        if (!is_array($filesResponse)) {
            $filesResponse = $this->safeApiCall(fn () => $this->fileService->list(['limit' => 5]));
            if ($filesResponse['ok'] ?? false) {
                $cache->save($filesCacheKey, $filesResponse, 60);
            }
        }
        $totalFiles = $this->extractTotal($filesResponse);

        $usersResponse = ['ok' => false, 'data' => []];
        if ($isAdmin) {
            $usersResponse = $cache->get('dashboard_users');
            if (!is_array($usersResponse)) {
                $usersResponse = $this->safeApiCall(fn () => $this->userService->list(['limit' => 1]));
                if ($usersResponse['ok'] ?? false) {
                    $cache->save('dashboard_users', $usersResponse, 120);
                }
            }
        }
        $totalUsers = $isAdmin ? $this->extractTotal($usersResponse) : 0;

        $uptime = $metrics['request_stats']['availability_percent']
               ?? $metrics['slo']['availability_percent']
               ?? null;

        $stats = [
            'users' => ['label' => lang('Dashboard.total_users'), 'value' => $totalUsers, 'icon' => 'users'],
            'files' => ['label' => lang('Dashboard.total_files'), 'value' => $totalFiles, 'icon' => 'files'],
        ];
        if ($uptime !== null) {
            $stats['uptime'] = ['label' => lang('Dashboard.api_uptime'), 'value' => $uptime . '%', 'icon' => 'activity'];
        }

        $devPanel = $this->renderDevApiErrorPanel($metricsResponse)
            . $this->renderDevApiErrorPanel($filesResponse)
            . ($isAdmin ? $this->renderDevApiErrorPanel($usersResponse) : '');

        return $this->response->setBody($devPanel . view('dashboard/partials/widget_stats', ['stats' => $stats]));
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

        return $this->response->setBody($devPanel . view('dashboard/partials/widget_health', [
            'healthServices' => $healthServices,
        ]));
    }

    public function widgetRecentFiles(): ResponseInterface
    {
        $userId        = (int) ((session('user') ?? [])['id'] ?? 0);
        $cache         = service('cache');
        $filesCacheKey = 'dashboard_files_' . $userId;

        $filesResponse = $cache->get($filesCacheKey);
        if (!is_array($filesResponse)) {
            $filesResponse = $this->safeApiCall(fn () => $this->fileService->list(['limit' => 5]));
            if ($filesResponse['ok'] ?? false) {
                $cache->save($filesCacheKey, $filesResponse, 60);
            }
        }

        return $this->response->setBody($this->renderDevApiErrorPanel($filesResponse) . view('dashboard/partials/widget_recent_files', [
            'recentFiles' => $this->extractItems($filesResponse),
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
        $cache    = service('cache');
        $devPanel = '';
        $items    = [];

        /** @var list<array{permission: string, cacheKey: string, call: callable(): array<string, mixed>, label: string, url: string, icon: string}> $resources */
        $resources = [
            [
                'permission' => 'cms.pages.read',
                'cacheKey'   => 'dashboard_count_pages',
                'call'       => fn () => $this->pageService->list(['limit' => 1]),
                'label'      => lang('Pages.pages_title'),
                'url'        => route_to('admin.cms.pages'),
                'icon'       => 'cms-page',
            ],
            [
                'permission' => 'cms.entries.read',
                'cacheKey'   => 'dashboard_count_entries',
                'call'       => fn () => $this->entryService->list(['limit' => 1]),
                'label'      => lang('Entries.entries_title'),
                'url'        => route_to('admin.cms.entries'),
                'icon'       => 'cms-entry',
            ],
            [
                'permission' => 'cms.collections.read',
                'cacheKey'   => 'dashboard_count_collections',
                'call'       => fn () => $this->collectionService->list(['limit' => 1]),
                'label'      => lang('Collections.collections_title'),
                'url'        => route_to('admin.cms.collections'),
                'icon'       => 'cms-collection',
            ],
            [
                'permission' => 'cms.menus.read',
                'cacheKey'   => 'dashboard_count_menus',
                'call'       => fn () => $this->menuService->list(['limit' => 1]),
                'label'      => lang('Menus.menus_title'),
                'url'        => route_to('admin.cms.menus'),
                'icon'       => 'cms-menu',
            ],
            [
                'permission' => 'cms.categories.read',
                'cacheKey'   => 'dashboard_count_categories',
                'call'       => fn () => $this->categoryService->list(['limit' => 1]),
                'label'      => lang('Categories.categories_title'),
                'url'        => route_to('admin.cms.categories'),
                'icon'       => 'folder-open',
            ],
            [
                'permission' => 'cms.tags.read',
                'cacheKey'   => 'dashboard_count_tags',
                'call'       => fn () => $this->tagService->list(['limit' => 1]),
                'label'      => lang('Tags.tags_title'),
                'url'        => route_to('admin.cms.tags'),
                'icon'       => 'tag',
            ],
        ];

        foreach ($resources as $resource) {
            if (! has_permission($resource['permission'])) {
                continue;
            }

            $response = $cache->get($resource['cacheKey']);
            if (!is_array($response)) {
                $response = $this->safeApiCall($resource['call']);
                if ($response['ok'] ?? false) {
                    $cache->save($resource['cacheKey'], $response, 300);
                }
            }
            $devPanel .= $this->renderDevApiErrorPanel($response);

            $items[] = [
                'label' => $resource['label'],
                'count' => $this->extractTotal($response),
                'url'   => $resource['url'],
                'icon'  => $resource['icon'],
                'badge' => null,
            ];
        }

        if (has_permission('cms.forms.read')) {
            // The domain's /cms/forms list endpoint is intentionally unpaginated
            // (FormService::list() ignores filters and always returns every
            // form), unlike Pages/Entries/etc which return a {data, meta}
            // envelope — so this can't reuse extractTotal()'s meta.total
            // lookup and instead counts the flat array directly.
            $formsCacheKey  = 'dashboard_count_forms';
            $formsResponse = $cache->get($formsCacheKey);
            if (!is_array($formsResponse)) {
                $formsResponse = $this->safeApiCall(fn () => $this->formService->list());
                if ($formsResponse['ok'] ?? false) {
                    $cache->save($formsCacheKey, $formsResponse, 300);
                }
            }
            $devPanel .= $this->renderDevApiErrorPanel($formsResponse);

            $items[] = [
                'label' => lang('Forms.title'),
                'count' => count($this->extractItems($formsResponse)),
                'url'   => route_to('admin.cms.forms'),
                'icon'  => 'clipboard-list',
                'badge' => null,
            ];
        }

        if (has_permission('cms.submissions.read')) {
            $countsCacheKey = 'dashboard_submission_counts';
            $countsResponse = $cache->get($countsCacheKey);
            if (!is_array($countsResponse)) {
                $countsResponse = $this->safeApiCall(fn () => $this->formSubmissionService->counts());
                if ($countsResponse['ok'] ?? false) {
                    $cache->save($countsCacheKey, $countsResponse, 60);
                }
            }
            $devPanel .= $this->renderDevApiErrorPanel($countsResponse);

            $counts  = $this->extractData($countsResponse);
            $pending = (int) ($counts['new'] ?? 0);
            $total   = array_sum(array_map(static fn ($value): int => (int) $value, $counts));

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

        return $this->response->setBody($devPanel . view('dashboard/partials/widget_summary', ['items' => $items]));
    }

    /**
     * Most recently updated Pages and Entries, merged and sorted — the
     * project-relevant equivalent of a generic "recent activity" feed.
     */
    public function widgetCmsActivity(): ResponseInterface
    {
        $cache    = service('cache');
        $devPanel = '';
        $entries  = [];

        if (has_permission('cms.pages.read')) {
            $response = $cache->get('dashboard_recent_pages');
            if (!is_array($response)) {
                $response = $this->safeApiCall(fn () => $this->pageService->list([
                    'limit' => 5,
                    'sort' => '-updated_at',
                    'include_translations' => 1,
                ]));
                if ($response['ok'] ?? false) {
                    $cache->save('dashboard_recent_pages', $response, 60);
                }
            }
            $devPanel .= $this->renderDevApiErrorPanel($response);
            foreach ($this->extractItems($response) as $page) {
                $entries[] = $this->buildActivityEntry(
                    $page,
                    lang('Translations.resource_page'),
                    route_to('admin.cms.pages.show', (string) ($page['id'] ?? ''))
                );
            }
        }

        if (has_permission('cms.entries.read')) {
            $response = $cache->get('dashboard_recent_entries');
            if (!is_array($response)) {
                $response = $this->safeApiCall(fn () => $this->entryService->list([
                    'limit' => 5,
                    'sort' => '-updated_at',
                    'include_translations' => 1,
                ]));
                if ($response['ok'] ?? false) {
                    $cache->save('dashboard_recent_entries', $response, 60);
                }
            }
            $devPanel .= $this->renderDevApiErrorPanel($response);
            foreach ($this->extractItems($response) as $entry) {
                $entries[] = $this->buildActivityEntry(
                    $entry,
                    lang('Translations.resource_entry'),
                    route_to('admin.cms.entries.show', (string) ($entry['id'] ?? ''))
                );
            }
        }

        usort($entries, static fn (array $a, array $b): int => strcmp((string) $b['updated_at'], (string) $a['updated_at']));

        return $this->response->setBody($devPanel . view('dashboard/partials/widget_cms_activity', [
            'items' => array_slice($entries, 0, 6),
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

    /**
     * @param array<string, mixed> $response
     */
    private function extractTotal(array $response): int
    {
        $payload = $response['data'] ?? [];

        return (int) ($payload['meta']['total'] ?? $payload['data']['meta']['total'] ?? $payload['total'] ?? 0);
    }
}
