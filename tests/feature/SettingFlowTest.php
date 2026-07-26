<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\SettingApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class SettingFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/settings');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/cms/settings');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.settings.read']],
        ])->get('/admin/cms/settings');

        $result->assertStatus(200);
    }

    public function testEditRendersAndUpdateRedirectsToList(): void
    {
        $mock = $this->createMock(SettingApiService::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 'test-uuid',
                    'setting_key' => 'site.title',
                    'setting_value' => 'Original title',
                    'setting_type' => 'string',
                    'setting_group' => 'general',
                    'is_translatable' => false,
                    'description' => 'Header title',
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $mock->expects($this->once())
            ->method('update')
            ->with('test-uuid', $this->callback(function (array $payload): bool {
                return ($payload['setting_key'] ?? null) === 'site.title'
                    && ($payload['setting_value'] ?? null) === 'Updated title'
                    && ($payload['setting_type'] ?? null) === 'string'
                    && ($payload['setting_group'] ?? null) === 'general'
                    && ($payload['is_translatable'] ?? null) === '0'
                    && ($payload['description'] ?? null) === 'Header title'
                    && ! array_key_exists('sort_order', $payload)
                    && ! array_key_exists('translations', $payload);
            }))
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['id' => 'test-uuid'],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('settingApiService', $mock);

        $session = [
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.settings.read', 'cms.settings.write']],
        ];

        $edit = $this->withSession($session)->get('/admin/cms/settings/test-uuid/edit');
        $edit->assertStatus(200);

        $body = (string) $edit->getBody();
        $this->assertStringContainsString('site.title', $body);
        $this->assertStringContainsString('Original title', $body);

        $update = $this->withSession($session)->post('/admin/cms/settings/test-uuid', [
            csrf_token() => csrf_hash(),
            'setting_key' => 'site.title',
            'setting_type' => 'string',
            'setting_value' => 'Updated title',
            'setting_value_string' => 'Updated title',
            'setting_group' => 'general',
            'is_translatable' => '0',
            'description' => 'Header title',
        ]);

        $update->assertRedirectTo(site_url('admin/cms/settings'));
    }

    public function testEditEchoesReturnToAndUpdateRedirectsBackToIt(): void
    {
        $mock = $this->createMock(SettingApiService::class);
        $mock->method('get')->willReturn([
            'ok' => true, 'status' => 200,
            'data' => [
                'id' => 'test-uuid', 'setting_key' => 'site.title', 'setting_value' => 'Original title',
                'setting_type' => 'string', 'setting_group' => 'general', 'is_translatable' => false,
                'description' => 'Header title',
            ],
            'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
        ]);
        $mock->method('update')->willReturn([
            'ok' => true, 'status' => 200, 'data' => ['id' => 'test-uuid'],
            'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
        ]);
        Services::injectMock('settingApiService', $mock);

        $session = [
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.settings.read', 'cms.settings.write']],
        ];
        $returnTo = '/admin/cms/translations/audit?resource=setting';

        $edit = $this->withSession($session)->get('/admin/cms/settings/test-uuid/edit?return_to=' . urlencode($returnTo));
        $edit->assertStatus(200);
        $this->assertStringContainsString('name="return_to" value="' . $returnTo . '"', (string) $edit->getBody());

        $update = $this->withSession($session)->post('/admin/cms/settings/test-uuid', [
            csrf_token() => csrf_hash(),
            'return_to' => $returnTo,
            'setting_key' => 'site.title',
            'setting_type' => 'string',
            'setting_value' => 'Updated title',
            'setting_value_string' => 'Updated title',
            'setting_group' => 'general',
            'is_translatable' => '0',
            'description' => 'Header title',
        ]);

        $update->assertRedirectTo(site_url($returnTo));
    }

    public function testUpdateIgnoresUnsafeReturnToAndFallsBackToDefault(): void
    {
        $mock = $this->createMock(SettingApiService::class);
        $mock->method('update')->willReturn([
            'ok' => true, 'status' => 200, 'data' => ['id' => 'test-uuid'],
            'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
        ]);
        Services::injectMock('settingApiService', $mock);

        $session = [
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.settings.read', 'cms.settings.write']],
        ];

        $update = $this->withSession($session)->post('/admin/cms/settings/test-uuid', [
            csrf_token() => csrf_hash(),
            'return_to' => 'https://evil.example.com/phish',
            'setting_key' => 'site.title',
            'setting_type' => 'string',
            'setting_value' => 'Updated title',
            'setting_value_string' => 'Updated title',
            'setting_group' => 'general',
            'is_translatable' => '0',
            'description' => 'Header title',
        ]);

        $update->assertRedirectTo(site_url('admin/cms/settings'));
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.settings.write']],
        ])->post('/admin/cms/settings', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(SettingApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('settingApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.settings.write', 'cms.settings.read']],
        ])->post('/admin/cms/settings/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/settings'));
    }
}
