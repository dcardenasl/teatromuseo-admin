<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
use App\Modules\Cms\Services\SettingApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/** @internal */
final class SiteIdentityFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testShowUsesOneBffBootstrapWithoutDirectReadFanout(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminCmsSiteIdentityBootstrap')
            ->willReturn($this->response([
                'settings' => [],
                'languages' => [],
            ]));
        Services::injectMock('bffApiClient', $bff);

        $settings = $this->createMock(SettingApiService::class);
        $settings->expects($this->never())->method('getByGroup');
        Services::injectMock('settingApiService', $settings);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.settings.read', 'cms.settings.write']],
        ])->get('/admin/cms/site-identity');

        $result->assertStatus(200);
    }

    public function testShowRendersConnectionErrorWithoutDirectReadFallback(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminCmsSiteIdentityBootstrap')
            ->willReturn([
                'ok' => false,
                'status' => 503,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => ['BFF unavailable'],
                'fieldErrors' => [],
            ]);
        Services::injectMock('bffApiClient', $bff);

        $settings = $this->createMock(SettingApiService::class);
        $settings->expects($this->never())->method('getByGroup');
        Services::injectMock('settingApiService', $settings);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.settings.read', 'cms.settings.write']],
        ])->get('/admin/cms/site-identity');

        $result->assertStatus(200);
        $this->assertStringContainsString('Error de conexión con el servidor.', html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** @param array<string, mixed> $sections */
    private function response(array $sections): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => ['data' => ['sections' => $sections]],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }
}
