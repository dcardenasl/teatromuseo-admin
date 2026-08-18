<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\BffApiClient as BffApiClientConfig;

/**
 * HTTP client targeting a ci4-bff-starter gateway.
 *
 * Reuses every behaviour of {@see ApiClient} (auth header injection,
 * app-key forwarding, upload handling) but defaults to the
 * `BffApiClient` config instead of `ApiClient`. Token refresh is
 * delegated to the Hub client — see {@see SecondaryApiClient} — because
 * the BFF never issues or refreshes JWTs itself (it only introspects them
 * against the Hub).
 *
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class BffApiClient extends SecondaryApiClient implements BffApiClientInterface
{
    /** @var list<string> */
    private const ADMIN_EVENT_LOOKUP_CONTEXTS = ['occurrence', 'ticket_type', 'ticket', 'booking', 'event_reference'];

    public function __construct(?BffApiClientConfig $config = null, ?ApiClientInterface $hubClient = null)
    {
        parent::__construct($config ?? config(BffApiClientConfig::class), $hubClient ?? service('apiClient'));
    }

    /**
     * Read the authenticated Admin dashboard aggregate from the BFF.
     *
     * The BFF owns the fan-out to Hub and the three domain applications. The
     * Admin still owns the outer cache and stale/cooldown policy.
     *
     * @return ApiResponse
     */
    public function getAdminDashboard(int $maxRetries = 2): array
    {
        try {
            $response = $this->request('GET', '/me/admin-dashboard', [
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin dashboard BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin dashboard BFF returned HTTP %d (access_token=%s).',
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    /**
     * Read the Hub-owned Metrics summary and trend series through one BFF
     * request. The Admin keeps direct calls as an availability fallback while
     * the BFF contract is rolled out.
     *
     * @return ApiResponse
     */
    public function getAdminMetricsWorkspace(string $period = '24h', int $maxRetries = 2): array
    {
        if (! in_array($period, ['1h', '24h', '7d', '30d'], true)) {
            return $this->unavailableResponse();
        }

        try {
            $response = $this->request('GET', '/me/admin-metrics/workspace', [
                'query' => ['period' => $period],
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin metrics workspace BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin metrics workspace BFF returned HTTP %d (access_token=%s).',
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    /** @return ApiResponse */
    public function getAdminIamRoleWorkspace(int|string $roleId, int $maxRetries = 2): array
    {
        $normalizedId = (string) $roleId;
        if ($normalizedId === '' || ! ctype_digit($normalizedId) || (int) $normalizedId < 1) {
            return $this->unavailableResponse();
        }

        try {
            $response = $this->request('GET', '/me/admin-iam/roles/' . $normalizedId . '/workspace', [
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin IAM role workspace BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        return $response;
    }

    /**
     * Read the complete authenticated Admin analytics projection from the BFF.
     *
     * The BFF owns the bounded CMS projection so the Analytics page does not
     * fan out into five individual CMS requests.
     *
     * @return ApiResponse
     */
    public function getAdminAnalytics(string $period = '7d', int $maxRetries = 2): array
    {
        try {
            $response = $this->request('GET', '/me/admin-analytics', [
                'query'      => ['period' => $period],
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin analytics BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin analytics BFF returned HTTP %d (access_token=%s).',
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    /**
     * Read complete-or-incomplete cross-domain file usages from the BFF.
     *
     * @param int|string $fileId
     * @return ApiResponse
     */
    public function getAdminFileUsages(int|string $fileId, int $maxRetries = 2): array
    {
        $normalizedId = (string) $fileId;
        if ($normalizedId === '' || ! ctype_digit($normalizedId) || (int) $normalizedId < 1) {
            return $this->unavailableResponse();
        }

        try {
            $response = $this->request('GET', '/me/admin-files/' . $normalizedId . '/usages', [
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin file usages BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin file usages BFF returned HTTP %d (access_token=%s).',
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    /** @return ApiResponse */
    public function getAdminEventLookups(string $context, int $maxRetries = 2): array
    {
        if (! in_array($context, self::ADMIN_EVENT_LOOKUP_CONTEXTS, true)) {
            return $this->unavailableResponse();
        }

        try {
            $response = $this->request('GET', '/me/admin-event-lookups/' . $context, [
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin Event lookup BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin Event lookup BFF returned HTTP %d (access_token=%s).',
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    /** @return ApiResponse */
    public function getAdminCatalogCollectionItemWorkspace(?int $itemId = null, int $maxRetries = 2): array
    {
        if ($itemId !== null && $itemId < 1) {
            return $this->unavailableResponse();
        }
        $path = $itemId === null
            ? '/me/admin-catalog/collection-items/workspace'
            : '/me/admin-catalog/collection-items/' . $itemId . '/workspace';

        return $this->adminWorkspaceRequest('Catalog collection item workspace', $path, $maxRetries);
    }

    /** @return ApiResponse */
    public function getAdminEventWorkspace(?int $eventId = null, int $maxRetries = 2): array
    {
        if ($eventId !== null && $eventId < 1) {
            return $this->unavailableResponse();
        }
        $path = $eventId === null
            ? '/me/admin-event/events/workspace'
            : '/me/admin-event/events/' . $eventId . '/workspace';

        return $this->adminWorkspaceRequest('Event workspace', $path, $maxRetries);
    }

    /** @return ApiResponse */
    public function getAdminCmsEntryFormOptions(?int $entryId = null, int $maxRetries = 2): array
    {
        $path = '/me/admin-cms/entry-form-options';
        if ($entryId !== null) {
            if ($entryId < 1) {
                return $this->unavailableResponse();
            }
            $path .= '/' . $entryId;
        }

        return $this->adminCmsRequest('entry form options', $path, $maxRetries);
    }

    /** @return ApiResponse */
    public function getAdminCmsPageFormOptions(?int $pageId = null, int $maxRetries = 2): array
    {
        $path = '/me/admin-cms/page-form-options';
        if ($pageId !== null) {
            if ($pageId < 1) {
                return $this->unavailableResponse();
            }
            $path .= '/' . $pageId;
        }

        return $this->adminCmsRequest('page form options', $path, $maxRetries);
    }

    /** @return ApiResponse */
    public function getAdminCmsMenuEditorBootstrap(int $menuId, ?int $itemId = null, int $maxRetries = 2): array
    {
        if ($menuId < 1 || ($itemId !== null && $itemId < 1)) {
            return $this->unavailableResponse();
        }

        $path = '/me/admin-cms/menus/' . $menuId . '/editor-bootstrap';
        if ($itemId !== null) {
            $path .= '/' . $itemId;
        }

        return $this->adminCmsRequest('menu editor bootstrap', $path, $maxRetries);
    }

    /** @return ApiResponse */
    public function getAdminCmsSiteIdentityBootstrap(int $maxRetries = 2): array
    {
        return $this->adminCmsRequest(
            'site identity bootstrap',
            '/me/admin-cms/site-identity-bootstrap',
            $maxRetries,
        );
    }

    /** @return ApiResponse */
    public function getAdminCmsPageWorkspace(int|string $pageId, ?int $instanceId = null, int $maxRetries = 2): array
    {
        $normalizedPageId = (string) $pageId;
        if ($normalizedPageId === '' || ! ctype_digit($normalizedPageId) || (int) $normalizedPageId < 1) {
            return $this->unavailableResponse();
        }
        if ($instanceId !== null && $instanceId < 1) {
            return $this->unavailableResponse();
        }

        $query = $instanceId === null ? [] : ['instance_id' => $instanceId];

        try {
            $response = $this->request('GET', '/me/admin-cms/pages/' . $normalizedPageId . '/workspace', [
                'query' => $query,
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin CMS page workspace BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin CMS page workspace BFF returned HTTP %d (access_token=%s).',
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    /** @return ApiResponse */
    public function getAdminCmsEntryWorkspace(int|string $entryId, ?int $instanceId = null, int $maxRetries = 2): array
    {
        $normalizedEntryId = (string) $entryId;
        if ($normalizedEntryId === '' || ! ctype_digit($normalizedEntryId) || (int) $normalizedEntryId < 1) {
            return $this->unavailableResponse();
        }
        if ($instanceId !== null && $instanceId < 1) {
            return $this->unavailableResponse();
        }

        try {
            return $this->request('GET', '/me/admin-cms/entries/' . $normalizedEntryId . '/workspace', [
                'query' => $instanceId === null ? [] : ['instance_id' => $instanceId],
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin CMS entry workspace BFF transport failure: %s: %s',
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }
    }

    /** @return ApiResponse */
    public function getAdminCmsWizardBootstrap(int $maxRetries = 2): array
    {
        return $this->adminCmsRequest('wizard bootstrap', '/me/admin-cms/wizard-bootstrap', $maxRetries);
    }

    /** @return ApiResponse */
    private function unavailableResponse(): array
    {
        return [
            'ok'          => false,
            'status'      => 0,
            'data'        => [],
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }

    /** @return ApiResponse */
    private function adminCmsRequest(string $label, string $path, int $maxRetries): array
    {
        try {
            $response = $this->request('GET', $path, [
                'max_retries' => $maxRetries,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin CMS %s BFF transport failure: %s: %s',
                $label,
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin CMS %s BFF returned HTTP %d (access_token=%s).',
                $label,
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    /** @return ApiResponse */
    private function adminWorkspaceRequest(string $label, string $path, int $maxRetries): array
    {
        try {
            $response = $this->request('GET', $path, ['max_retries' => $maxRetries], true);
        } catch (\Throwable $exception) {
            log_message('error', sprintf(
                'Admin %s BFF transport failure: %s: %s',
                $label,
                $exception::class,
                $exception->getMessage(),
            ));

            return $this->unavailableResponse();
        }

        if (($response['ok'] ?? false) !== true) {
            log_message('error', sprintf(
                'Admin %s BFF returned HTTP %d (access_token=%s).',
                $label,
                (int) ($response['status'] ?? 0),
                $this->hasAccessToken() ? 'present' : 'missing',
            ));
        }

        return $response;
    }

    private function hasAccessToken(): bool
    {
        $token = $this->session->get(\App\Support\SessionKeys::ACCESS_TOKEN->value);

        return is_string($token) && $token !== '';
    }
}
