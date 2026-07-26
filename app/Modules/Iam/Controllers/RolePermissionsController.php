<?php

declare(strict_types=1);

namespace App\Modules\Iam\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Iam\Services\RoleApiService;
use App\Modules\Iam\Services\RoleMatrixApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RolePermissionsController extends BaseWebController
{
    private RoleMatrixApiService $matrixService;
    private RoleApiService $roleService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->matrixService = service('roleMatrixApiService');
        $this->roleService = service('roleApiService');
    }

    public function index(): string
    {
        $response = $this->safeApiCall(fn () => $this->matrixService->matrix());
        $matrix = $this->extractData($response);

        return $this->render('iam/role_permissions/index', [
            'title'       => lang('Iam.role_permissions_title'),
            'applications' => $matrix['applications'] ?? [],
            'roles'       => $matrix['roles'] ?? [],
            'assignments' => $matrix['assignments'] ?? [],
            'activeRoleId' => (string) ($this->request->getGet('tab') ?? (($matrix['roles'][0]['id'] ?? '') ?: '')),
            'error'       => ($response['ok'] ?? false) ? null : $this->firstMessage($response, lang('Iam.role_permissions_load_failed')),
        ]);
    }

    public function save(string $roleId): RedirectResponse
    {
        $permissionIds = $this->request->getPost('permission_ids');
        $ids = is_array($permissionIds)
            ? array_values(array_unique(array_filter(array_map('intval', $permissionIds), static fn (int $id): bool => $id > 0)))
            : [];

        $rawCode = $this->request->getPost('code');
        $rawName = $this->request->getPost('name');
        $rawDescription = $this->request->getPost('description');
        $rawApplicationId = $this->request->getPost('application_id');
        $applicationId = is_scalar($rawApplicationId) && trim((string) $rawApplicationId) !== ''
            ? (int) $rawApplicationId
            : null;

        $payload = [
            'code'           => is_scalar($rawCode) ? (string) $rawCode : '',
            'name'           => is_scalar($rawName) ? (string) $rawName : '',
            'description'    => is_scalar($rawDescription) ? (string) $rawDescription : '',
            'application_id' => $applicationId,
            'permission_ids' => $ids,
        ];

        $response = $this->safeApiCall(fn () => $this->roleService->update($roleId, $payload));
        if (! ($response['ok'] ?? false)) {
            return $this->failApi($response, lang('Iam.role_permissions_save_failed'), route_to('admin.iam.role_permissions') . '?tab=' . $roleId, false);
        }

        service('permissionsSessionRefresher')->forceRefresh();

        return redirect()->to(route_to('admin.iam.role_permissions') . '?tab=' . $roleId)
            ->with('success', lang('Iam.role_permissions_save_success'));
    }
}
