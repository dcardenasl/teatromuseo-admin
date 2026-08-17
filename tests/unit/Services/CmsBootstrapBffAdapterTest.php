<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\BffApiClientInterface;
use App\Modules\Cms\Services\CmsBootstrapBffAdapter;
use CodeIgniter\Test\CIUnitTestCase;

final class CmsBootstrapBffAdapterTest extends CIUnitTestCase
{
    public function testNormalizesTheNestedBffEnvelope(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminCmsPageFormOptions')
            ->with(35)
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'status' => 'success',
                    'data' => [
                        'sections' => [
                            'languages' => [['id' => 1, 'code' => 'es']],
                            'pages' => [],
                            'collections' => [],
                        ],
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        $result = (new CmsBootstrapBffAdapter($bff))->pageFormOptions(35);

        $this->assertSame([['id' => 1, 'code' => 'es']], $result['languages']);
    }

    public function testUnavailableBffResponseReturnsNullForFallback(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->method('getAdminCmsSiteIdentityBootstrap')->willReturn([
            'ok' => false,
            'status' => 503,
            'data' => [],
            'raw' => '',
            'headers' => [],
            'messages' => ['unavailable'],
            'fieldErrors' => [],
        ]);

        $this->assertNull((new CmsBootstrapBffAdapter($bff))->siteIdentityBootstrap());
    }

    public function testNormalizesWizardBootstrapSections(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminCmsWizardBootstrap')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['sections' => ['config' => ['languages' => []], 'blockTypes' => []]],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        $result = (new CmsBootstrapBffAdapter($bff))->wizardBootstrap();

        $this->assertSame([], $result['blockTypes']);
    }
}
