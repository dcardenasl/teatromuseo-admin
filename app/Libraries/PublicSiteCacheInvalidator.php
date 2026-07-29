<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use Config\App as AppConfig;

class PublicSiteCacheInvalidator
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $invalidateKey,
        private readonly int $timeout = 5,
    ) {
    }

    /**
     * Ask the public website to drop cached API responses for the given scopes.
     *
     * This is best-effort only. A cache invalidation failure should never block
     * the editor from saving content successfully.
     *
     * @param list<string> $scopes
     */
    public function invalidate(array $scopes): bool
    {
        $normalizedScopes = $this->normalizeScopes($scopes);
        if ($normalizedScopes === []) {
            return true;
        }

        if (trim($this->baseUrl) === '' || trim($this->invalidateKey) === '') {
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Missing PUBLIC_SITE_URL or CACHE_INVALIDATE_KEY. '
                . 'Skipping cache invalidation for scopes: ' . implode(', ', $normalizedScopes)
            );

            return false;
        }

        try {
            $appConfig = config(AppConfig::class);
            $baseUrl   = rtrim($this->baseUrl, '/');
            $client    = new CURLRequest(
                $appConfig,
                new URI($baseUrl),
                new Response($appConfig),
                [
                    'baseURI'         => $baseUrl,
                    'timeout'         => max(1, $this->timeout),
                    'connect_timeout' => max(1, $this->timeout),
                    'http_errors'     => false,
                ]
            );

            $response = $client->request('POST', '/cache/invalidate', [
                'headers' => [
                    'Accept'           => 'application/json',
                    'Content-Type'     => 'application/json',
                    'X-Invalidate-Key' => $this->invalidateKey,
                ],
                'json' => ['scopes' => $normalizedScopes],
            ]);
        } catch (\Throwable $e) {
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Cache invalidation request failed: ' . $e->getMessage()
            );

            return false;
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $body = trim((string) $response->getBody());
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Public cache invalidation failed with HTTP ' . $status
                . ' for scopes: ' . implode(', ', $normalizedScopes)
                . ($body !== '' ? '. Body: ' . $this->compactBody($body) : '')
            );

            return false;
        }

        log_message(
            'info',
            '[PublicSiteCacheInvalidator] Invalidated scopes: ' . implode(', ', $normalizedScopes)
        );

        return true;
    }

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes): array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $scope = trim((string) $scope);
            if ($scope === '') {
                continue;
            }

            $normalized[$scope] = true;
        }

        return array_keys($normalized);
    }

    private function compactBody(string $body): string
    {
        if (strlen($body) <= 500) {
            return $body;
        }

        return substr($body, 0, 500) . '...';
    }
}
