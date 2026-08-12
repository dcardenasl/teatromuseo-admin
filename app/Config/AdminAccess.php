<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Admin section UI gating.
 *
 * `AdminFilter` is a broad section gate for route groups that explicitly opt
 * into it. Domain resource modules must use `auth` plus a fine-grained
 * `permission:<code>` filter on every endpoint and do not depend on this list.
 *
 * When a new section-gated admin module is added:
 *  1. Define its permissions in the API's RbacBootstrapSeeder.
 *  2. Add the module's *.read permission code to $permissions here.
 *  3. Keep the module's own per-route permission filters explicit.
 *
 * Centralizing this list here (instead of hardcoding in AdminFilter)
 * prevents the gate from drifting out of sync with the API.
 */
class AdminAccess extends BaseConfig
{
    /**
     * Permission codes whose presence grants entry to the admin section.
     * Override via the env var `ADMIN_PERMISSIONS` (comma-separated) when
     * a deployment adds custom admin-only modules.
     *
     * @var list<string>
     */
    public array $permissions = [
        'users.read',
        'audit.read',
        'apikeys.read',
        'metrics.read',
        'system.public-cache.read',
        'iam.admin-access',
        'cms.languages.read',
        'cms.settings.read',
        'cms.pages.read',
        'cms.menus.read',
        'cms.blocks.read',
        'cms.collections.read',
        'cms.entries.read',
        'cms.categories.read',
        'cms.tags.read',
        'cms.redirects.read',
        'cms.forms.read',
        'cms.submissions.read',
    ];

    public function __construct()
    {
        parent::__construct();

        $envValue = (string) (env('ADMIN_PERMISSIONS', '') ?? '');
        if ($envValue === '') {
            return;
        }

        $codes = array_values(array_filter(array_map(
            static fn (string $code): string => trim($code),
            explode(',', $envValue)
        ), static fn (string $code): bool => $code !== ''));

        if ($codes !== []) {
            $this->permissions = $codes;
        }
    }
}
