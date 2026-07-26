<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\EntryStoreRequest;
use App\Modules\Cms\Requests\EntryUpdateRequest;
use App\Modules\Cms\Services\CategoryApiService;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\TagApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EntryController extends BaseWebController
{
    protected EntryApiService $entryService;
    protected CollectionApiService $collectionService;
    protected CategoryApiService $categoryService;
    protected TagApiService $tagService;
    protected TranslationAuditApiService $translationAuditService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->entryService      = service('entryApiService');
        $this->collectionService = service('collectionApiService');
        $this->categoryService   = service('categoryApiService');
        $this->tagService        = service('tagApiService');
        $this->translationAuditService = service('translationAuditApiService');
    }

    public function index(): string
    {
        return $this->render('cms/entries/index', [
            'title'        => lang('Entries.entries_title'),
            'limitOptions' => [10, 25, 50, 100],
            'collections' => $this->collectionsOptions(),
            'languages'   => $this->getLanguages(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['collection_id'],
            ['name', 'created_at'],
            fn (array $params) => $this->entryService->list([...$params, 'include_translations' => 1]),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->entryService->get($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);

            return $this->render('cms/entries/show', [
                'title' => lang('Entries.entries_details'),
                'entry' => [],
                'collection' => [],
                'languages' => [],
                'error' => $this->firstMessage($response, lang('Entries.entries_not_found')),
                'collections' => $this->collectionsOptions(),
                'blocks' => [],
                'blockTypes' => [],
                'blockTranslationStatus' => [],
                'publicSiteUrl' => '',
            ]);
        }

        $entryData = $this->extractData($response);
        $collectionId = $entryData['collection_id'] ?? '';
        $collection = [];
        if ($collectionId !== '') {
            $colResponse = $this->safeApiCall(fn () => $this->collectionService->get((string) $collectionId));
            if ($colResponse['ok']) {
                $collection = $this->extractData($colResponse);
            } else {
                $this->maybeFlashDevError($colResponse);
            }
        }

        return $this->render('cms/entries/show', [
            'title' => lang('Entries.entries_details'),
            'entry' => $entryData,
            'collection' => $collection,
            'languages' => $this->getLanguages(),
            'collections' => $this->collectionsOptions(),
            'blocks' => $this->entryBlocks($id),
            'blockTypes' => $this->fetchBlockTypesIndexed(),
            'blockTranslationStatus' => $this->ownerBlockTranslationStatus('entry', $id),
            'publicSiteUrl' => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
        ]);
    }

    /**
     * Translation status for every top-level/child block of this entry.
     * Degrades to empty on API failure so a status outage never breaks the
     * entry detail page itself.
     *
     * @return array<int|string, array<string, array<string, mixed>>>
     */
    private function ownerBlockTranslationStatus(string $ownerType, string $ownerId): array
    {
        $response = $this->safeApiCall(fn () => $this->translationAuditService->auditOwnerBlocks($ownerType, $ownerId));
        $data = $response['ok'] ? $this->extractData($response) : [];

        return is_array($data['blocks'] ?? null) ? $data['blocks'] : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entryBlocks(string $id): array
    {
        $response = $this->safeApiCall(fn () => service('blockInstanceApiService')->list($id, 'entry', ['sort' => 'sort_order']));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);

            return [];
        }

        $blocks = $this->extractItems($response);

        return array_values(array_filter($blocks, static fn (array $b) => empty($b['parent_instance_id'])));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchBlockTypesIndexed(): array
    {
        $types = service('blockCatalogService')->indexed();

        $indexed = [];
        foreach ((array) $types as $t) {
            if (is_array($t) && isset($t['id'])) {
                $indexed[(int) $t['id']] = $t;
            }
        }

        return $indexed;
    }

    public function create(): string
    {
        $collectionId = $this->request->getGet('collection_id');
        $item = [];
        if ($collectionId !== null && is_scalar($collectionId) && (int) $collectionId > 0) {
            $item['collection_id'] = (int) $collectionId;
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['title', 'excerpt', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/entries/create', [
            'title'            => lang('Entries.entries_create'),
            'collections'      => $this->collectionsOptions(),
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex'  => $languageContext['defaultLangIndex'],
            'item'             => $item,
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var EntryStoreRequest $request */
        $request = service('formRequest', EntryStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->entryService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.entries'))->with('success', lang('Entries.entries_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->get($id));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);

            return $this->withError(lang('Entries.entries_not_found'), route_to('admin.cms.entries'));
        }

        $item           = $this->extractData($response);
        $blockTemplate  = $this->resolveBlockTemplate($item);
        $languages      = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId  = $languageContext['defaultLangId'];
        $fieldMap       = ['title', 'excerpt', 'meta_title', 'meta_description'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        return $this->render('cms/entries/edit', [
            'title'            => lang('Entries.entries_edit'),
            'item'             => $item,
            'collections'      => $this->collectionsOptions(),
            'languages'        => $languages,
            'focusLangId'      => $focusLangId,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'blockTemplate'    => $blockTemplate,
            'translateTargets' => $translateTargets,
            'returnTo'         => $this->incomingReturnTo(),
            ...$this->taxonomyOptions($item),
        ]);
    }

    /**
     * Fetches block_template from the entry's parent collection (null if none).
     *
     * @param array<string, mixed> $item
     * @return array<string, mixed>|null
     */
    private function resolveBlockTemplate(array $item): ?array
    {
        $collectionId = $item['collection_id'] ?? null;
        if (empty($collectionId)) {
            return null;
        }

        $response = $this->safeApiCall(fn () => $this->collectionService->get((string) $collectionId));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);

            return null;
        }

        $collection = $this->extractData($response);
        $template   = $collection['block_template'] ?? null;

        if (!is_array($template) || empty($template['blocks'])) {
            return null;
        }

        return $template;
    }

    public function update(string $id): RedirectResponse
    {
        /** @var EntryUpdateRequest $request */
        $request = service('formRequest', EntryUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->entryService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_update_failed'));
        }

        $taxonomyResponse = $this->syncTaxonomy($id);
        if (! $taxonomyResponse['ok']) {
            return $this->failApi($taxonomyResponse, lang('Entries.entries_taxonomy_update_failed'));
        }

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.entries')))->with('success', lang('Entries.entries_update_success'));
    }

    /**
     * @param array<string, mixed> $entry
     * @return array{categoryOptions: array<string, string>, tagOptions: array<string, string>, selectedCategoryIds: list<int>, selectedTagIds: list<int>}
     */
    private function taxonomyOptions(array $entry): array
    {
        $selectedCategoryIds = $this->taxonomyIds($entry['categories'] ?? []);
        $selectedTagIds = $this->taxonomyIds($entry['tags'] ?? []);

        /** @var array<string, string> $categoryOptions */
        $categoryOptions = $this->taxonomyLabels($entry['categories'] ?? []);
        /** @var array<string, string> $tagOptions */
        $tagOptions = $this->taxonomyLabels($entry['tags'] ?? []);

        $collectionId = isset($entry['collection_id']) ? (int) $entry['collection_id'] : 0;
        $categories = $this->safeApiCall(fn () => $this->categoryService->list(['per_page' => 1000]));
        if (! $categories['ok']) {
            $this->maybeFlashDevError($categories);
        }
        foreach ($this->extractItems($categories) as $category) {
            if (! is_array($category) || ! isset($category['id'])) {
                continue;
            }
            if ($collectionId > 0 && (int) ($category['collection_id'] ?? 0) !== $collectionId) {
                continue;
            }
            $categoryOptions[(string) $category['id']] = $this->taxonomyLabel($category);
        }

        $tags = $this->safeApiCall(fn () => $this->tagService->list(['per_page' => 1000]));
        if (! $tags['ok']) {
            $this->maybeFlashDevError($tags);
        }
        foreach ($this->extractItems($tags) as $tag) {
            if (! is_array($tag) || ! isset($tag['id'])) {
                continue;
            }
            $tagOptions[(string) $tag['id']] = $this->taxonomyLabel($tag);
        }

        return compact('categoryOptions', 'tagOptions', 'selectedCategoryIds', 'selectedTagIds');
    }

    /**
     * @param mixed $items
     * @return list<int>
     */
    private function taxonomyIds(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        /** @var list<int> $ids */
        $ids = [];
        foreach ($items as $item) {
            $id = is_array($item) ? ($item['id'] ?? null) : $item;
            if (is_numeric($id) && (int) $id > 0) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param mixed $items
     * @return array<string, string>
     */
    private function taxonomyLabels(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        /** @var array<string, string> $labels */
        $labels = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $labels[(string) $item['id']] = $this->taxonomyLabel($item);
        }

        return $labels;
    }

    /** @param array<string, mixed> $item */
    private function taxonomyLabel(array $item): string
    {
        return (string) ($item['name'] ?? $item['title'] ?? $item['slug'] ?? $item['id'] ?? '');
    }

    /** @return array<string, mixed> */
    private function syncTaxonomy(string $id): array
    {
        $categoryIds = $this->postedTaxonomyIds('category_ids');
        $tagIds = $this->postedTaxonomyIds('tag_ids');
        return $this->safeApiCall(fn () => $this->entryService->syncTaxonomy($id, $categoryIds, $tagIds));
    }

    /** @return list<int> */
    private function postedTaxonomyIds(string $field): array
    {
        $raw = $this->request->getPost($field);
        if (! is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $id) {
            if (is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_delete_failed'), route_to('admin.cms.entries'), false);
        }

        return redirect()->to(route_to('admin.cms.entries'))->with('success', lang('Entries.entries_delete_success'));
    }



    public function checkSlug(): ResponseInterface
    {
        $slugRaw       = $this->request->getGet('slug');
        $languageIdRaw = $this->request->getGet('language_id');
        $currentIdRaw  = $this->request->getGet('current_id');
        $slug          = is_scalar($slugRaw) ? (string) $slugRaw : '';
        $languageId    = is_scalar($languageIdRaw) ? (int)    $languageIdRaw : 0;
        $currentId     = is_scalar($currentIdRaw) ? (string) $currentIdRaw : '';

        if ($slug === '' || $languageId === 0) {
            return $this->response->setJSON(['available' => false]);
        }

        $result = $this->safeApiCall(fn () => $this->entryService->checkSlug($slug, $languageId, $currentId));
        if (! $result['ok']) {
            $this->maybeFlashDevError($result);
        }
        $data   = $this->extractData($result);
        return $this->response->setJSON(['available' => (bool) ($data['available'] ?? false)]);
    }

    public function reorder(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $collections = $this->collectionsOptions();
        $collectionIdParam = $this->request->getGet('collection_id');
        $collectionId = is_numeric($collectionIdParam) ? (int) $collectionIdParam : null;

        if ($collectionId === null && ! empty($collections)) {
            $collectionId = array_key_first($collections);
        }

        $items = [];
        if ($collectionId !== null) {
            $response = $this->safeApiCall(fn () => $this->entryService->list([
                'limit' => 250,
                'sort' => 'sort_order',
                'filter' => [
                    'collection_id' => (int) $collectionId,
                ],
            ]));
            if (! $response['ok']) {
                $this->maybeFlashDevError($response);
            }
            $items = $this->extractItems($response);
        }

        return $this->render('cms/entries/reorder', [
            'title' => lang('Entries.entries_title') . ' - ' . lang('Entries.field_sort_order'),
            'items' => $items,
            'collections' => $collections,
            'selectedCollectionId' => $collectionId,
        ]);
    }

    public function saveOrder(): ResponseInterface
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => lang('App.access_denied'),
            ])->setStatusCode(403);
        }

        $request = $this->request;
        if (! $request instanceof \CodeIgniter\HTTP\IncomingRequest) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Invalid request type',
            ])->setStatusCode(400);
        }

        $json = $request->getJSON(true);
        $jsonArray = is_array($json) ? $json : [];
        $items = $jsonArray['items'] ?? [];

        if (! is_array($items)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Invalid payload structure',
            ])->setStatusCode(400);
        }

        foreach ($items as $item) {
            $id = (string) ($item['id'] ?? '');
            $value = isset($item['sort_order']) ? (int) $item['sort_order'] : 0;

            if ($id !== '') {
                $this->entryService->update($id, ['sort_order' => $value]);
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Files.gallery_save_success') ?? 'Order saved.',
        ]);
    }


    public function publish(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->publish($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_publish_failed'), route_to('admin.cms.entries.show', $id), false);
        }

        return redirect()->to(route_to('admin.cms.entries.show', $id))->with('success', lang('Entries.entries_publish_success'));
    }

    public function archive(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->entryService->archive($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Entries.entries_archive_failed'), route_to('admin.cms.entries.show', $id), false);
        }

        return redirect()->to(route_to('admin.cms.entries.show', $id))->with('success', lang('Entries.entries_archive_success'));
    }


    /** @return array<string, string> */
    private function collectionsOptions(): array
    {
        $response = $this->safeApiCall(fn () => $this->entryService->collections(['limit' => 100, 'is_active' => true]));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
        }
        $options = [];

        foreach ($this->extractItems($response) as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            $label = $item['collection_key'] ?? $item['name'] ?? $item['title'] ?? $item['label'] ?? $item['id'];
            $options[(string) $item['id']] = (string) $label;
        }

        return $options;
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.entries.write')) {
            return redirect()->to(route_to('admin.cms.entries'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
        }
        return $this->extractItems($response);
    }
}
