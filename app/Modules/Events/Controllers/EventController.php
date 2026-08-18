<?php

declare(strict_types=1);

namespace App\Modules\Events\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Events\Requests\EventStoreRequest;
use App\Modules\Events\Requests\EventUpdateRequest;
use App\Modules\Events\Services\EventApiServiceInterface;
use App\Modules\Events\Services\EventWorkspaceBffAdapter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EventController extends BaseWebController
{
    protected EventApiServiceInterface $eventService;
    protected EventWorkspaceBffAdapter $workspaceAdapter;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->eventService = service('eventApiService');
        $this->workspaceAdapter = service('eventWorkspaceBffAdapter');
    }

    public function index(): string
    {
        return $this->render('events/events/index', [
            'title'        => lang('Events.events_title'),
            'limitOptions' => [10, 25, 50, 100],
            'eventTypeLabels' => $this->eventTypeLabels(),

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->eventService->list([...$params, 'projection' => 'list']),
        );
    }

    public function show(string $id): string
    {
        $workspace = $this->workspaceAdapter->workspace((int) $id);
        if ($workspace !== null && is_array($workspace['event'] ?? null)) {
            return $this->render('events/events/show', [
                'title' => lang('Events.events_details'),
                'event' => $workspace['event'],
                'eventTypeLabels' => $this->eventTypeLabelsFromWorkspace($workspace['eventTypes'] ?? []),
            ]);
        }
        if (! $this->workspaceAdapter->wasUnavailable()) {
            return $this->render('events/events/show', [
                'title' => lang('Events.events_details'),
                'event' => [],
                'eventTypeLabels' => [],
                'error' => lang('Events.events_not_found'),
            ]);
        }

        $response = $this->safeApiCall(fn () => $this->eventService->get($id));

        if (! $response['ok']) {
            return $this->render('events/events/show', [
                'title' => lang('Events.events_details'),
                'event' => [],
                'eventTypeLabels' => $this->eventTypeLabels(),
                'error' => $this->firstMessage($response, lang('Events.events_not_found')),

            ]);
        }

        return $this->render('events/events/show', [
            'title' => lang('Events.events_details'),
            'event' => $this->extractData($response),
            'eventTypeLabels' => $this->eventTypeLabels(),

        ]);
    }

    public function create(): string
    {
        $workspace = $this->workspaceAdapter->workspace();
        $languageContext = $this->contentLanguageContext(
            languages: $workspace !== null && is_array($workspace['languages'] ?? null)
                ? $workspace['languages']
                : null,
        );

        return $this->render('events/events/create', [
            'title' => lang('Events.events_create'),
            'eventTypeOptions' => $workspace !== null
                ? $this->eventTypeLabelsFromWorkspace($workspace['eventTypes'] ?? [])
                : $this->eventTypeLabels(),
            ...$languageContext,

        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var EventStoreRequest $request */
        $request = service('formRequest', EventStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.events_create_failed'));
        }

        $this->invalidatePublicSiteCache(['events', 'event_types']);

        return redirect()->to(route_to('admin.events.events'))->with('success', lang('Events.events_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $workspace = $this->workspaceAdapter->workspace((int) $id);
        if ($workspace !== null && is_array($workspace['event'] ?? null)) {
            $item = $workspace['event'];
            return $this->render('events/events/edit', [
                'title' => lang('Events.events_edit'),
                'item' => $item,
                'eventTypeOptions' => $this->eventTypeLabelsFromWorkspace($workspace['eventTypes'] ?? []),
                ...$this->contentLanguageContext(
                    $item,
                    is_array($workspace['languages'] ?? null) ? $workspace['languages'] : [],
                ),
            ]);
        }
        if (! $this->workspaceAdapter->wasUnavailable()) {
            return $this->withError(lang('Events.events_not_found'), route_to('admin.events.events'));
        }

        $response = $this->safeApiCall(fn () => $this->eventService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Events.events_not_found'), route_to('admin.events.events'));
        }

        return $this->render('events/events/edit', [
            'title' => lang('Events.events_edit'),
            'item'  => $this->extractData($response),
            'eventTypeOptions' => $this->eventTypeLabels(),
            ...$this->contentLanguageContext($this->extractData($response)),

        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var EventUpdateRequest $request */
        $request = service('formRequest', EventUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->eventService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.events_update_failed'));
        }

        $this->invalidatePublicSiteCache(['events', 'event_types']);

        return redirect()->to(route_to('admin.events.events'))->with('success', lang('Events.events_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->eventService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Events.events_delete_failed'), route_to('admin.events.events'), false);
        }

        $this->invalidatePublicSiteCache(['events', 'event_types']);

        return redirect()->to(route_to('admin.events.events'))->with('success', lang('Events.events_delete_success'));
    }

    /**
     * Read the CMS language registry for every form render. The fallback keeps
     * the Event UI usable during a CMS outage, while the API still remains the
     * source of truth for persisted translations.
     *
     * @param array<string, mixed> $item
     * @param array<int|string, mixed>|null $languages
     * @return array{languages: list<array<string, mixed>>, defaultLangCode: string, defaultLangIndex: int, translations: array<string, array<string, string>>}
     */
    private function contentLanguageContext(array $item = [], ?array $languages = null): array
    {
        if ($languages === null) {
            $response = $this->safeApiCall(
                fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true])
            );
            $languages = [];

            foreach ($this->extractItems($response) as $language) {
                if (! is_array($language) || ! isset($language['code']) || ! is_scalar($language['code'])) {
                    continue;
                }

                $code = strtolower(str_replace('_', '-', trim((string) $language['code'])));
                if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/', $code) !== 1) {
                    continue;
                }

                $language['code'] = $code;
                $languages[] = $language;
            }
        }

        $languages = array_values(array_filter(
            $languages,
            static fn (mixed $language): bool => is_array($language),
        ));

        if ($languages === []) {
            $fallbackCode = strtolower((string) ($this->viewData['currentLocale'] ?? 'es'));
            $languages[] = [
                'code' => $fallbackCode,
                'name' => strtoupper($fallbackCode),
                'native_name' => strtoupper($fallbackCode),
                'is_default' => true,
            ];
        }

        $defaultIndex = 0;
        foreach ($languages as $index => $language) {
            if (! empty($language['is_default'])) {
                $defaultIndex = (int) $index;
                break;
            }
        }

        $translationValues = [];
        foreach (is_array($item['translations'] ?? null) ? $item['translations'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $locale = isset($row['locale']) && is_scalar($row['locale'])
                ? strtolower(str_replace('_', '-', trim((string) $row['locale'])))
                : '';
            if ($locale === '') {
                continue;
            }

            $fields = is_array($row['fields'] ?? null) ? $row['fields'] : $row;
            $translationValues[$locale] = [
                'title' => is_scalar($fields['title'] ?? null) ? (string) $fields['title'] : '',
                'description' => is_scalar($fields['description'] ?? null) ? (string) $fields['description'] : '',
            ];
        }

        return [
            'languages' => $languages,
            'defaultLangCode' => (string) ($languages[$defaultIndex]['code'] ?? ''),
            'defaultLangIndex' => $defaultIndex,
            'translations' => $translationValues,
        ];
    }

    /** @return array<string, string> */
    private function eventTypeLabels(): array
    {
        $response = $this->safeApiCall(fn () => $this->eventService->listTypes());
        $labels = [];

        foreach ($this->extractItems($response) as $type) {
            if (! is_array($type)) {
                continue;
            }

            $slug = trim((string) ($type['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $localized = is_array($type['localized'] ?? null) ? $type['localized'] : [];
            $labels[$slug] = (string) ($localized['name'] ?? $type['name'] ?? $slug);
        }

        return $labels;
    }

    /**
     * @param mixed $items
     * @return array<string,string>
     */
    private function eventTypeLabelsFromWorkspace(mixed $items): array
    {
        $labels = [];
        if (! is_array($items)) {
            return $labels;
        }
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $slug = trim((string) ($item['slug'] ?? ''));
            if ($slug !== '') {
                $labels[$slug] = (string) ($item['name'] ?? $slug);
            }
        }

        return $labels;
    }




}
