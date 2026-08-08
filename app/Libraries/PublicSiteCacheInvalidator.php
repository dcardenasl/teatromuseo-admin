<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;

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
        return $this->invalidateWithResult($scopes, 'admin_content_write')['ok'];
    }

    /**
     * Invalidate public-site cache and return the remote operation details.
     *
     * @param list<string> $scopes
     * @return array{ok: bool, status: int, invalidated: list<string>, deleted: int, message: string|null}
     */
    public function invalidateWithResult(array $scopes, string $source = 'admin_manual'): array
    {
        $normalizedScopes = $this->normalizeScopes($scopes);
        if ($normalizedScopes === []) {
            return [
                'ok' => true,
                'status' => 200,
                'invalidated' => [],
                'deleted' => 0,
                'message' => null,
            ];
        }

        if (trim($this->baseUrl) === '' || trim($this->invalidateKey) === '') {
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Missing PUBLIC_SITE_URL or CACHE_INVALIDATE_KEY. '
                . 'Skipping cache invalidation for scopes: ' . implode(', ', $normalizedScopes)
            );

            return [
                'ok' => false,
                'status' => 0,
                'invalidated' => [],
                'deleted' => 0,
                'message' => 'Cache invalidation is not configured.',
            ];
        }

        try {
            $response = $this->buildClient()->request('POST', '/cache/invalidate', [
                'headers' => [
                    'Accept'           => 'application/json',
                    'Content-Type'     => 'application/json',
                    'X-Invalidate-Key' => $this->invalidateKey,
                    'X-Cache-Invalidation-Source' => trim($source) !== '' ? trim($source) : 'admin_manual',
                ],
                'json' => ['scopes' => $normalizedScopes],
            ]);
        } catch (\Throwable $e) {
            log_message(
                'warning',
                '[PublicSiteCacheInvalidator] Cache invalidation request failed: ' . $e->getMessage()
            );

            return [
                'ok' => false,
                'status' => 0,
                'invalidated' => [],
                'deleted' => 0,
                'message' => $e->getMessage(),
            ];
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

            return [
                'ok' => false,
                'status' => $status,
                'invalidated' => [],
                'deleted' => 0,
                'message' => $body !== '' ? $this->compactBody($body) : 'Public cache invalidation failed.',
            ];
        }

        log_message(
            'info',
            '[PublicSiteCacheInvalidator] Invalidated scopes: ' . implode(', ', $normalizedScopes)
        );

        $decoded = json_decode((string) $response->getBody(), true);
        $decoded = is_array($decoded) ? $decoded : [];

        return [
            'ok' => true,
            'status' => $status,
            'invalidated' => $this->stringList($decoded['invalidated'] ?? $normalizedScopes),
            'deleted' => max(0, (int) ($decoded['deleted'] ?? 0)),
            'message' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        if (trim($this->baseUrl) === '' || trim($this->invalidateKey) === '') {
            return [
                'ok' => false,
                'status' => 0,
                'data' => [],
                'message' => 'Public site cache invalidation is not configured.',
            ];
        }

        try {
            $response = $this->buildClient()->request('GET', '/cache/status', [
                'headers' => [
                    'Accept' => 'application/json',
                    'X-Invalidate-Key' => $this->invalidateKey,
                ],
            ]);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'data' => [],
                'message' => $e->getMessage(),
            ];
        }

        $status = $response->getStatusCode();
        $decoded = json_decode((string) $response->getBody(), true);
        $decoded = is_array($decoded) ? $decoded : [];

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($decoded['data'] ?? null) ? $decoded['data'] : [],
            'message' => $status >= 200 && $status < 300 ? null : 'Could not read public-site cache status.',
        ];
    }

    private function buildClient(): CURLRequest
    {
        $baseUrl = rtrim($this->baseUrl, '/');

        return \Config\Services::curlrequest([
            'baseURI'         => $baseUrl,
            'timeout'         => max(1, $this->timeout),
            'connect_timeout' => max(1, $this->timeout),
            'http_errors'     => false,
        ]);
    }

    public const VALID_SCOPES = [
        'settings',
        'menus',
        'pages',
        'collections',
        'entries',
        'taxonomies',
        'events',
        'event_types',
        'categories',
        'techniques',
        'collection_items',
        'redirects',
        'forms',
    ];

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes): array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $scope = trim((string) $scope);
            if ($scope === '' || ! in_array($scope, self::VALID_SCOPES, true)) {
                if ($scope !== '') {
                    log_message('warning', '[PublicSiteCacheInvalidator] Invalid scope omitted: ' . $scope);
                }
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

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn (mixed $item): string => trim((string) $item), $value),
            static fn (string $item): bool => $item !== '',
        ));
    }
}
