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
 */
class BffApiClient extends SecondaryApiClient implements BffApiClientInterface
{
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
     * @return array<string, mixed>
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
     * Read the complete authenticated Admin analytics projection from the BFF.
     *
     * The BFF owns the bounded CMS projection so the Analytics page does not
     * fan out into five individual CMS requests.
     *
     * @return array<string, mixed>
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

    /** @return array<string, mixed> */
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

    private function hasAccessToken(): bool
    {
        $token = $this->session->get(\App\Support\SessionKeys::ACCESS_TOKEN->value);

        return is_string($token) && $token !== '';
    }
}
