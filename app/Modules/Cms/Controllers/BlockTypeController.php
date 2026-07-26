<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\BlockTypeStoreRequest;
use App\Modules\Cms\Requests\BlockTypeUpdateRequest;
use App\Modules\Cms\Services\BlockTypeApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BlockTypeController extends BaseWebController
{
    protected BlockTypeApiService $blockTypeService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->blockTypeService = service('blockTypeApiService');
    }

    public function index(): string
    {
        return $this->render('cms/block_types/index', [
            'title'        => lang('BlockTypes.block_types_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    // Manual escape hatch for the block-catalog cache (BlockCatalogService,
    // 2 min TTL): a schema change made outside this admin's own edit form —
    // a domain migration or seed, a direct API call — has no way to notify
    // this cache. Any cms-admin can hit this instead of waiting out the TTL
    // or asking someone to run `php spark cache:clear` on the server.
    public function refreshCache(): RedirectResponse
    {
        $this->invalidateBlockCatalogCache();

        return redirect()->to(route_to('admin.cms.block_types'))->with('success', lang('BlockTypes.block_types_cache_refreshed'));
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->blockTypeService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->blockTypeService->get($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->render('cms/block_types/show', [
                'title' => lang('BlockTypes.block_types_details'),
                'blockType' => [],
                'error' => $this->firstMessage($response, lang('BlockTypes.block_types_not_found')),

            ]);
        }

        $usagesResponse = $this->safeApiCall(fn () => $this->blockTypeService->usages($id));
        $usages         = ($usagesResponse['ok'] ?? false) ? $this->extractItems($usagesResponse) : [];
        $usages         = array_map(fn (array $usage) => array_merge($usage, [
            'edit_url' => $this->resolveUsageEditUrl(
                is_array($usage['context'] ?? null) ? (array) $usage['context'] : [],
                (int) ($usage['resource_id'] ?? 0),
            ),
        ]), array_values($usages));

        return $this->render('cms/block_types/show', [
            'title' => lang('BlockTypes.block_types_details'),
            'blockType' => $this->extractData($response),
            'usages' => $usages,

        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveUsageEditUrl(array $context, int $instanceId): ?string
    {
        $ownerType = (string) ($context['owner_type'] ?? '');
        $ownerId   = (int) ($context['owner_id'] ?? 0);

        if ($ownerId <= 0) {
            return null;
        }

        return match ($ownerType) {
            'page' => site_url('admin/cms/pages/' . $ownerId . '/blocks/' . $instanceId . '/edit'),
            'entry' => site_url('admin/cms/entries/' . $ownerId . '/blocks/' . $instanceId . '/edit'),
            default => null,
        };
    }

    public function create(): string
    {
        $templates = $this->blockTypeService->templates();
        $listResponse = $this->safeApiCall(fn () => $this->blockTypeService->list(['limit' => 100]));
        $this->maybeFlashDevError($listResponse);
        $blockTypes = $listResponse['ok'] ? $this->extractItems($listResponse) : [];

        return $this->render('cms/block_types/create', [
            'title'      => lang('BlockTypes.block_types_create'),
            'templates'  => $templates,
            'sourceKinds' => $this->sourceKinds(),
            'blockTypes' => $blockTypes,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var BlockTypeStoreRequest $request */
        $request = service('formRequest', BlockTypeStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->blockTypeService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('BlockTypes.block_types_create_failed'));
        }

        $this->invalidateBlockCatalogCache();

        return redirect()->to(route_to('admin.cms.block_types'))->with('success', lang('BlockTypes.block_types_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->blockTypeService->get($id));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->withError(lang('BlockTypes.block_types_not_found'), route_to('admin.cms.block_types'));
        }

        $templates = $this->blockTypeService->templates();
        $listResponse = $this->safeApiCall(fn () => $this->blockTypeService->list(['limit' => 100]));
        $this->maybeFlashDevError($listResponse);
        $blockTypes = $listResponse['ok'] ? $this->extractItems($listResponse) : [];

        return $this->render('cms/block_types/edit', [
            'title'      => lang('BlockTypes.block_types_edit'),
            'item'       => $this->extractData($response),
            'templates'  => $templates,
            'sourceKinds' => $this->sourceKinds(),
            'blockTypes' => $blockTypes,
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var BlockTypeUpdateRequest $request */
        $request = service('formRequest', BlockTypeUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->blockTypeService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('BlockTypes.block_types_update_failed'));
        }

        $this->invalidateBlockCatalogCache();

        return redirect()->to(route_to('admin.cms.block_types'))->with('success', lang('BlockTypes.block_types_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->blockTypeService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('BlockTypes.block_types_delete_failed'), route_to('admin.cms.block_types'), false);
        }

        $this->invalidateBlockCatalogCache();

        return redirect()->to(route_to('admin.cms.block_types'))->with('success', lang('BlockTypes.block_types_delete_success'));
    }

    private function invalidateBlockCatalogCache(): void
    {
        cache()->delete('cms_block_types_active_catalog');
        cache()->delete('cms_block_types_template_catalog');
    }

    /**
     * @return array<int, array{key: string, label: string, description: string}>
     */
    private function sourceKinds(): array
    {
        return [
            ['key' => 'manual', 'label' => lang('BlockTypes.source_manual'), 'description' => lang('BlockTypes.source_manual_desc')],
            ['key' => 'page', 'label' => lang('BlockTypes.source_page'), 'description' => lang('BlockTypes.source_page_desc')],
            ['key' => 'collection', 'label' => lang('BlockTypes.source_collection'), 'description' => lang('BlockTypes.source_collection_desc')],
            ['key' => 'entry', 'label' => lang('BlockTypes.source_entry'), 'description' => lang('BlockTypes.source_entry_desc')],
            ['key' => 'container', 'label' => lang('BlockTypes.source_container'), 'description' => lang('BlockTypes.source_container_desc')],
        ];
    }




}
