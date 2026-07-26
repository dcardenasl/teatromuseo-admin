<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\TranslationAuditApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class TranslationAuditFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testReportForwardsAllContextualFilters(): void
    {
        $audit = $this->createMock(TranslationAuditApiService::class);
        $audit->expects($this->once())->method('getReport')->with([
            'language_id' => 3,
            'resource' => 'page',
            'status' => 'missing',
            'search' => 'home',
        ])->willReturn([
            'ok' => true, 'status' => 200, 'data' => ['data' => [['resource' => 'page']]],
            'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
        ]);
        Services::injectMock('translationAuditApiService', $audit);

        $response = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.languages.read']],
        ])->get('/admin/cms/translations/audit/data?language_id=3&resource=page&status=missing&search=home');

        $response->assertStatus(200);
        $this->assertStringContainsString('"resource": "page"', (string) $response->getBody());
    }

    public function testReportRequiresLanguageReadPermission(): void
    {
        $response = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => []],
        ])->get('/admin/cms/translations/audit/data');

        $response->assertRedirect();
    }
}
