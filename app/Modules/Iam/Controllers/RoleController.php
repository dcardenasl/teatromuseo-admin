<?php

declare(strict_types=1);

namespace App\Modules\Iam\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Iam\Requests\RoleStoreRequest;
use App\Modules\Iam\Requests\RoleUpdateRequest;
use App\Modules\Iam\Services\PermissionApiService;
use App\Modules\Iam\Services\RoleApiService;
use App\Modules\Iam\Support\IamLookups;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RoleController extends BaseWebController
{
    protected RoleApiService $roleService;
    protected PermissionApiService $permissionService;
    protected IamLookups $lookups;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->roleService       = service('roleApiService');
        $this->permissionService = service('permissionApiService');
        $this->lookups           = new IamLookups();
    }

    public function index(): string
    {
        return $this->render('iam/roles/index', [
            'title'        => lang('Iam.roles_title'),
            'limitOptions' => [10, 25, 50, 100],
        ]);
    }

    public function data(): ResponseInterface
    {
        $tableState = $this->resolveTableState([], ['name', 'code', 'application_id', 'created_at']);
        $params     = $this->buildTableApiParams($tableState);
        $response   = $this->safeApiCall(fn () => $this->roleService->list($params));

        unset($response['raw']);

        return $this->passthroughApiJsonResponse($response);
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->roleService->get($id));

        if (! ($response['ok'] ?? false)) {
            return $this->render('iam/roles/show', [
                'title' => lang('Iam.roles_details'),
                'role'  => [],
                'error' => $this->firstMessage($response, lang('Iam.roles_not_found')),
            ]);
        }

        $assignedResponse       = $this->safeApiCall(fn () => $this->roleService->listPermissions($id));
        $allPermissionsResponse = $this->safeApiCall(fn () => $this->permissionService->list(['limit' => 200]));

        $assignedPermissions = $this->extractItems($assignedResponse);
        $allPermissions      = $this->extractItems($allPermissionsResponse);
        $assignedIds         = array_map(static fn (array $p): int => (int) ($p['id'] ?? 0), $assignedPermissions);

        $role = $this->extractData($response);

        return $this->render('iam/roles/show', [
            'title'                 => lang('Iam.roles_details'),
            'role'                  => $role,
            'allPermissions'        => $allPermissions,
            'assignedPermissionIds' => $assignedIds,
        ]);
    }

    public function create(): string
    {
        return $this->render('iam/roles/create', [
            'title'                 => lang('Iam.roles_create'),
            'applications'          => $this->lookups->applications(),
            'allPermissions'        => $this->loadAllPermissions(),
            'assignedPermissionIds' => [],
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var RoleStoreRequest $request */
        $request = service('formRequest', RoleStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->roleService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Iam.roles_create_failed'));
        }

        service('permissionsSessionRefresher')->forceRefresh();

        return redirect()->to(route_to('admin.iam.roles'))->with('success', lang('Iam.roles_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->roleService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Iam.roles_not_found'), route_to('admin.iam.roles'));
        }

        $assignedResponse = $this->safeApiCall(fn () => $this->roleService->listPermissions($id));
        $assignedItems    = $this->extractItems($assignedResponse);
        $assignedIds      = array_map(static fn (array $p): int => (int) ($p['id'] ?? 0), $assignedItems);

        return $this->render('iam/roles/edit', [
            'title'                 => lang('Iam.roles_edit'),
            'item'                  => $this->extractData($response),
            'applications'          => $this->lookups->applications(),
            'allPermissions'        => $this->loadAllPermissions(),
            'assignedPermissionIds' => $assignedIds,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadAllPermissions(): array
    {
        $all = [];
        $page = 1;

        do {
            $response = $this->safeApiCall(fn () => $this->permissionService->list([
                'page'     => $page,
                'per_page' => 500,
            ]));

            $items = $this->extractItems($response);
            $all = array_merge($all, $items);
            $meta = $response['data']['meta'] ?? $response['data'] ?? [];
            $total = (int) ($meta['total'] ?? count($all));
            $perPage = (int) ($meta['per_page'] ?? 500);
            $page++;
        } while ($perPage > 0 && count($all) < $total);

        return $all;
    }

    public function update(string $id): RedirectResponse
    {
        /** @var RoleUpdateRequest $request */
        $request = service('formRequest', RoleUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->roleService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Iam.roles_update_failed'));
        }

        service('permissionsSessionRefresher')->forceRefresh();

        return redirect()->to(route_to('admin.iam.roles'))->with('success', lang('Iam.roles_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->roleService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Iam.roles_delete_failed'), route_to('admin.iam.roles'), false);
        }

        service('permissionsSessionRefresher')->forceRefresh();

        return redirect()->to(route_to('admin.iam.roles'))->with('success', lang('Iam.roles_delete_success'));
    }

    public function attachPermissions(string $id): RedirectResponse
    {
        $rawIds = $this->request->getPost('permission_ids');
        $ids    = is_array($rawIds) ? array_values(array_map('intval', $rawIds)) : [];

        if ($ids === []) {
            return redirect()->to(route_to('admin.iam.roles.show', $id))
                ->with('error', lang('Iam.permissions_attach_select_required'));
        }

        $response = $this->safeApiCall(fn () => $this->roleService->attachPermissions($id, $ids));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Iam.permissions_attach_failed'), route_to('admin.iam.roles.show', $id), false);
        }

        service('permissionsSessionRefresher')->forceRefresh();

        return redirect()->to(route_to('admin.iam.roles.show', $id))
            ->with('success', lang('Iam.permissions_attach_success'));
    }

    public function detachPermission(string $id, string $permissionId): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->roleService->detachPermission($id, $permissionId));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Iam.permissions_detach_failed'), route_to('admin.iam.roles.show', $id), false);
        }

        service('permissionsSessionRefresher')->forceRefresh();

        return redirect()->to(route_to('admin.iam.roles.show', $id))
            ->with('success', lang('Iam.permissions_detach_success'));
    }
}
