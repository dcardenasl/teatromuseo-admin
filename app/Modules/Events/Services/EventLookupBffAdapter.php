<?php

declare(strict_types=1);

namespace App\Modules\Events\Services;

use App\Libraries\BffApiClientInterface;

/** Normalizes the bounded Event lookup projection for Admin form consumers. */
final class EventLookupBffAdapter
{
    public function __construct(private readonly BffApiClientInterface $bffApiClient)
    {
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>, raw: string, headers: array<string, string>, messages: list<string>, fieldErrors: array<string, string>}
     */
    public function read(string $context): array
    {
        $response = $this->bffApiClient->getAdminEventLookups($context);
        if (($response['ok'] ?? false) !== true) {
            return $response;
        }

        $payload  = is_array($response['data'] ?? null) ? $response['data'] : [];
        $sections = is_array($payload['data']['sections'] ?? null) ? $payload['data']['sections'] : [];
        $source   = is_array($payload['data']['source'] ?? null) ? $payload['data']['source'] : [];

        $response['data'] = [
            'context'  => (string) ($payload['data']['context'] ?? $context),
            'source'   => $source,
            'sections' => $sections,
        ];

        return $response;
    }

    /**
     * @param array<string, mixed> $response
     * @return list<array<string, mixed>>
     */
    public function section(array $response, string $section): array
    {
        if (($response['ok'] ?? false) !== true) {
            return [];
        }

        $data     = is_array($response['data'] ?? null) ? $response['data'] : [];
        $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];
        $items    = $sections[$section] ?? [];

        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }

    /**
     * @param array<string, mixed> $response
     */
    public function sourceAvailable(array $response): bool
    {
        if (($response['ok'] ?? false) !== true) {
            return false;
        }

        $data   = is_array($response['data'] ?? null) ? $response['data'] : [];
        $source = is_array($data['source'] ?? null) ? $data['source'] : [];

        return ($source['state'] ?? 'unavailable') === 'ok';
    }
}
