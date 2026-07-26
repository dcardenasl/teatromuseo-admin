<?php

declare(strict_types=1);

namespace Tests\Contract;

use PHPUnit\Framework\TestCase;

final class DomainCmsApiContractTest extends TestCase
{
    public function testCmsPagesRouteKeepsTheAuthenticationEnvelope(): void
    {
        if (getenv('RUN_CONTRACT_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_CONTRACT_TESTS=1 and provide an isolated Domain endpoint.');
        }

        $baseUrl = rtrim((string) (getenv('DOMAIN_CONTRACT_BASE_URL') ?: ''), '/');
        $this->assertNotSame('', $baseUrl, 'DOMAIN_CONTRACT_BASE_URL is required.');

        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
        $body = @file_get_contents($baseUrl . '/api/v1/cms/pages', false, $context);
        $this->assertNotFalse($body, 'Domain contract endpoint is unreachable.');

        $headers = $http_response_header ?? [];
        $this->assertMatchesRegularExpression('#^HTTP/\S+ 401\b#', $headers[0] ?? '');

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($payload);
        $this->assertSame('error', $payload['status'] ?? null);
        $this->assertSame(401, $payload['code'] ?? null);
    }
}
