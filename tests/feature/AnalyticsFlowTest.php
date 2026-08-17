<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/** @internal */
final class AnalyticsFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAnalyticsPageUsesOneCompleteBffProjection(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminAnalytics')
            ->with('24h')
            ->willReturn($this->bffResponse('24h'));
        Services::injectMock('bffApiClient', $bff);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => [
                'id' => 1,
                'first_name' => 'Admin',
                'permissions' => ['cms.analytics.read'],
            ],
        ])->get('/admin/analytics?period=24h');

        $result->assertStatus(200);
        $this->assertStringContainsString('24', $result->getBody());
        $this->assertStringContainsString('/inicio', $result->getBody());
        $this->assertStringContainsString('google.com', $result->getBody());
    }

    /** @return array<string, mixed> */
    private function bffResponse(string $period): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'status' => 'success',
                'data' => [
                    'version' => 1,
                    'source' => ['cms' => 'ok', 'state' => 'ok'],
                    'sections' => [
                        'overview' => [
                            'total_views' => 12,
                            'unique_visitors' => 5,
                            'top_page' => '/inicio',
                            'top_page_title' => 'Inicio',
                            'top_referrer' => 'google.com',
                            'period' => $period,
                        ],
                        'pages' => ['data' => [['url' => '/inicio', 'page_title' => 'Inicio', 'views' => 10, 'percentage' => 83.3]]],
                        'referrers' => ['data' => [['domain' => 'google.com', 'views' => 8, 'percentage' => 66.7]]],
                        'devices' => ['desktop' => 8, 'mobile' => 2, 'tablet' => 0, 'bot' => 0, 'unknown' => 0],
                        'timeseries' => ['data' => [['label' => '2026-08-17', 'views' => 12, 'unique_visitors' => 5]]],
                    ],
                ],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }
}
