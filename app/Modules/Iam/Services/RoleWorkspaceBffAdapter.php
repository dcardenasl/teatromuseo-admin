<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Libraries\BffApiClientInterface;
use App\Support\BffAvailability;

/** Normalizes the Hub IAM role workspace for existing Admin views. */
final class RoleWorkspaceBffAdapter
{
    private bool $unavailable = false;

    public function __construct(private readonly BffApiClientInterface $bffClient)
    {
    }

    /** @return array<string, mixed>|null */
    public function read(int|string $roleId): ?array
    {
        $response = $this->bffClient->getAdminIamRoleWorkspace($roleId);
        $this->unavailable = BffAvailability::isUnavailable($response);
        if ($this->unavailable || ($response['ok'] ?? false) !== true) {
            return null;
        }

        $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

        return [
            'role' => is_array($data['role'] ?? null) ? $data['role'] : [],
            'allPermissions' => is_array($data['allPermissions'] ?? null) ? $data['allPermissions'] : [],
            'assignedPermissionIds' => is_array($data['assignedPermissionIds'] ?? null) ? $data['assignedPermissionIds'] : [],
        ];
    }

    public function wasUnavailable(): bool
    {
        return $this->unavailable;
    }
}
