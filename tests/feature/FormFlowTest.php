<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Fixtures\AdminFixtureFactory;

/**
 * @internal
 */
final class FormFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/forms');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms');

        $result->assertStatus(200);
        $result->assertSee(lang('Forms.title'));
    }

    public function testShowRendersForAdmin(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $formId = $fixtures->id('form');
        $language = $fixtures->languages(1)[0];
        $formKey = $fixtures->value('form-key');
        $formName = $fixtures->value('form-name');
        $fieldId = $fixtures->id('field');
        $fieldLabel = $fixtures->value('field-label');
        $formMock = $this->createMock(FormApiService::class);
        $formMock->method('get')
            ->with((string) $formId)
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => $formId,
                    'form_key' => $formKey,
                    'is_active' => true,
                    'has_captcha' => false,
                    'notify_email' => 'admin@test.com',
                    'autoreply_enabled' => false,
                    'autoreply_email_field' => null,
                    'created_at' => '2026-06-25 00:00:00',
                    'translations' => [
                        [
                            'language_id' => $language['id'],
                            'name' => $formName,
                            'submit_label' => 'Submit',
                            'description' => 'Send us a message',
                            'success_message' => 'Thanks!',
                            'error_message' => 'Error!'
                        ]
                    ],
                    'fields' => [
                        [
                            'id' => $fieldId,
                            'field_key' => 'email',
                            'field_type' => 'email',
                            'is_required' => true,
                            'translations' => [
                                [
                                    'language_id' => $language['id'],
                                    'label' => $fieldLabel,
                                    'placeholder' => 'Enter your email',
                                    'help_text' => 'Must be valid'
                                ]
                            ]
                        ]
                    ]
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['items' => [$language]],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        Services::injectMock('formApiService', $formMock);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms/' . $formId);

        $result->assertStatus(200);
        $result->assertSee($formName);
        $result->assertSee($formKey);
        $result->assertSee('admin@test.com');
        $result->assertSee($fieldLabel);
    }

    public function testShowRendersLinkedUsagesAndOpenEditorLink(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $formId = $fixtures->id('form');
        $language = $fixtures->languages(1)[0];
        $formKey = $fixtures->value('form-key');
        $usageLabel = $fixtures->value('usage-label');
        $ownerId = $fixtures->id('owner-page');
        $blockId = $fixtures->id('block');
        $formMock = $this->createMock(FormApiService::class);
        $formMock->method('get')
            ->with((string) $formId)
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => $formId,
                    'form_key' => $formKey,
                    'is_active' => true,
                    'has_captcha' => false,
                    'notify_email' => null,
                    'autoreply_enabled' => false,
                    'autoreply_email_field' => null,
                    'created_at' => '2026-06-25 00:00:00',
                    'translations' => [
                        [
                            'language_id' => $language['id'],
                            'name' => $fixtures->value('form-name'),
                            'submit_label' => 'Submit',
                            'description' => null,
                            'success_message' => 'Thanks!',
                            'error_message' => 'Error!'
                        ]
                    ],
                    'fields' => [],
                    'usages' => [
                        [
                            'resource' => 'block_instances',
                            'resource_id' => $blockId,
                            'role' => 'page',
                            'label' => $usageLabel,
                            'context' => [
                                'owner_type' => 'page',
                                'owner_id' => $ownerId,
                                'block_key' => 'form_embed',
                                'block_name' => 'Formulario Embebido',
                            ],
                            'edit_url' => 'http://localhost:8182/admin/cms/pages/' . $ownerId . '/blocks/' . $blockId . '/edit',
                        ],
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'items' => [$language]
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        Services::injectMock('formApiService', $formMock);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms/' . $formId);

        $result->assertStatus(200);
        $result->assertSee(lang('Forms.usages_title'));
        $result->assertSee(lang('Forms.usage_page'));
        $result->assertSee($usageLabel);
        $result->assertSee(lang('Forms.usage_edit'));
        $result->assertSee('http://localhost:8182/admin/cms/pages/' . $ownerId . '/blocks/' . $blockId . '/edit');
        $result->assertDontSee('/admin/cms/forms/' . $formId . '/delete');
    }

    public function testShowFormNotFoundRendersError(): void
    {
        $formMock = $this->createMock(FormApiService::class);
        $formMock->method('get')
            ->with('999')
            ->willReturn([
                'ok' => false,
                'status' => 404,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => ['Form not found.'],
                'fieldErrors' => []
            ]);

        Services::injectMock('formApiService', $formMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms/999');

        $result->assertStatus(200);
        $result->assertSee('Form not found.');
    }
}
