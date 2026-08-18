<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class MetricsFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testMetricsPageRendersSummaryAndTimeseries(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminMetricsWorkspace')
            ->with('24h')
            ->willReturn($this->bffResponse([
                'summary' => [
                    'request_stats' => ['total_requests' => 321, 'avg_response_time_ms' => 87, 'availability_percent' => 99.2, 'successful_requests' => 315],
                    'slow_requests' => [
                        ['method' => 'GET', 'uri' => '/api/v1/slow-route', 'response_time' => 1500],
                    ],
                ],
                'timeseries' => [['period' => '10:00', 'value' => 12, 'errors' => 0, 'latency' => 45]],
            ]));

        Services::injectMock('bffApiClient', $bff);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['users.read', 'users.write', 'audit.read', 'metrics.read', 'apikeys.read', 'apikeys.write', 'iam.superadmin-access']],
        ])->get('/admin/metrics');

        $result->assertStatus(200);
        $this->assertStringContainsString('321', $result->getBody());
        $this->assertStringContainsString('10:00', $result->getBody());
        $this->assertStringContainsString('/api/v1/slow-route', $result->getBody());
        $this->assertStringContainsString('1500 ms', $result->getBody());
    }

    public function testMetricsPageFallsBackToDefaultPeriodWhenFilterIsInvalid(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminMetricsWorkspace')
            ->with('24h')
            ->willReturn($this->bffResponse(['summary' => [], 'timeseries' => []]));

        Services::injectMock('bffApiClient', $bff);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['users.read', 'users.write', 'audit.read', 'metrics.read', 'apikeys.read', 'apikeys.write', 'iam.superadmin-access']],
        ])->get('/admin/metrics?period=invalid');

        $result->assertStatus(200);
    }

    /** @param array<string, mixed> $sections */
    private function bffResponse(array $sections): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => ['data' => $sections],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }
}
