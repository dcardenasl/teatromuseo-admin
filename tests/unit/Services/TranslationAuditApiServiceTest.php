<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\TranslationAuditApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TranslationAuditApiServiceTest extends CIUnitTestCase
{
    public function testAuditOwnerBlocksCallsTheOwnerScopedEndpoint(): void
    {
        $expected = [
            'ok'          => true,
            'status'      => 200,
            'data'        => [
                'data' => [
                    'blocks' => [
                        73 => ['es' => ['language_id' => 3, 'status' => 'complete', 'detail' => '']],
                    ],
                    'summary' => ['es' => ['complete' => 1, 'total' => 1]],
                ],
            ],
            'raw'         => '',
            'messages'    => [],
            'fieldErrors' => [],
        ];

        $mock = $this->createMock(ApiClientInterface::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/translations/audit/owner/entry/4')
            ->willReturn($expected);

        $service = new TranslationAuditApiService($mock);
        $result = $service->auditOwnerBlocks('entry', '4');

        $this->assertTrue($result['ok']);
        $this->assertSame(['complete' => 1, 'total' => 1], $result['data']['data']['summary']['es']);
    }

    public function testAuditOwnerBlocksAcceptsAnIntegerOwnerId(): void
    {
        $mock = $this->createMock(ApiClientInterface::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/translations/audit/owner/page/19')
            ->willReturn(['ok' => true, 'status' => 200, 'data' => [], 'raw' => '', 'messages' => [], 'fieldErrors' => []]);

        $service = new TranslationAuditApiService($mock);
        $service->auditOwnerBlocks('page', 19);
    }
}
