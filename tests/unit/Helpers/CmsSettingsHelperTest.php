<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CmsSettingsHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('cms_settings');
    }

    public function testCmsSettingResolutionPrefersTranslationMetadata(): void
    {
        $setting = [
            'setting_key' => 'site_name',
            'description' => 'Fallback label',
            'translations' => [
                [
                    'language_id' => 2,
                    'label' => 'Localized label',
                    'placeholder' => 'Localized placeholder',
                    'help_text' => 'Localized help',
                    'setting_value' => 'My Site',
                ],
            ],
        ];

        $this->assertSame('Localized label', cms_setting_resolve_label($setting));
        $this->assertSame('Localized placeholder', cms_setting_resolve_placeholder($setting));
        $this->assertSame('Localized help', cms_setting_resolve_help($setting));
        $this->assertSame('My Site', cms_setting_translation_value($setting, 2));
        $this->assertSame('', cms_setting_translation_value($setting, 99));
    }

    public function testCmsSettingsBuildTranslationPanelNormalizesRowsAndTargets(): void
    {
        $settings = [
            [
                'setting_key' => 'site_name',
                'description' => 'Site name',
                'input_type' => 'text',
                'is_translatable' => 1,
                'translations' => [
                    [
                        'language_id' => 2,
                        'setting_value' => 'My Site',
                    ],
                ],
            ],
            [
                'setting_key' => 'site_logo',
                'description' => 'Logo',
                'input_type' => 'image',
                'is_translatable' => 0,
            ],
        ];

        $languages = [
            ['id' => 1, 'code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'is_default' => 1],
            ['id' => 2, 'code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => 0],
            ['id' => 3, 'code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'is_default' => 0],
        ];

        $panel = cms_settings_build_translation_panel($settings, $languages, 1);

        $this->assertSame(1, $panel['activeLanguageId']);
        $this->assertSame('ES', $panel['defaultLanguageCode']);
        $this->assertCount(3, $panel['translationLanguages']);
        $this->assertSame('My Site', $panel['rowsByLanguage'][2][0]['value']);
        $this->assertSame('[name="site_name_value"]', $panel['translateTargetsByLanguageId'][2][0]['from']);
        $this->assertSame('[name="site_name_translations[2]"]', $panel['translateTargetsByLanguageId'][2][0]['to']);
        $this->assertCount(2, $panel['translateTargets']);
        $this->assertSame(1, $panel['translatableFieldCount']);
    }
}
