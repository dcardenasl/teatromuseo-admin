<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\SiteIdentityUpdateRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class SiteIdentityUpdateRequestTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPayloadBuildsPlainValueForNonTranslatableSetting(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'site_name_value' => 'Teatro Museo',
        ]);

        $formRequest = new SiteIdentityUpdateRequest($request, service('validation'));
        $formRequest->setIdentitySettings([
            [
                'setting_key'     => 'site_name',
                'input_type'      => 'text',
                'is_translatable' => false,
                'setting_value'   => 'Old name',
            ],
        ]);

        $payload = $formRequest->payload();

        $this->assertSame(['setting_value' => 'Teatro Museo'], $payload['site_name']);
    }

    public function testPayloadPreservesCanonicalValueWhenFieldOmitted(): void
    {
        $request = service('request');
        $request->setGlobal('post', []);

        $formRequest = new SiteIdentityUpdateRequest($request, service('validation'));
        $formRequest->setIdentitySettings([
            [
                'setting_key'     => 'site_name',
                'input_type'      => 'text',
                'is_translatable' => false,
                'setting_value'   => 'Canonical name',
            ],
        ]);

        $payload = $formRequest->payload();

        $this->assertSame(['setting_value' => 'Canonical name'], $payload['site_name']);
    }

    public function testPayloadIncludesTranslationsForTranslatableSetting(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'tagline_value' => 'Base tagline',
            'tagline_translations' => [
                2 => 'Translated tagline',
                3 => '',
            ],
        ]);

        $formRequest = new SiteIdentityUpdateRequest($request, service('validation'));
        $formRequest->setIdentitySettings([
            [
                'setting_key'     => 'tagline',
                'input_type'      => 'text',
                'is_translatable' => true,
                'setting_value'   => 'Old tagline',
            ],
        ]);
        $formRequest->setLanguages([
            ['id' => 1, 'code' => 'es', 'is_default' => 1],
            ['id' => 2, 'code' => 'en', 'is_default' => 0],
            ['id' => 3, 'code' => 'fr', 'is_default' => 0],
        ]);
        $formRequest->setBaseLanguageId(1);

        $payload = $formRequest->payload();

        $this->assertSame('Base tagline', $payload['tagline']['setting_value']);
        $this->assertSame([
            ['language_id' => 2, 'setting_value' => 'Translated tagline'],
        ], $payload['tagline']['translations']);
    }

    public function testPayloadBuildsFilePayloadForImageInputType(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'logo_file_id'   => 'file-uuid-123',
            'logo_url'       => 'https://example.com/logo.png',
            'logo_mime_type' => 'image/png',
        ]);

        $formRequest = new SiteIdentityUpdateRequest($request, service('validation'));
        $formRequest->setIdentitySettings([
            [
                'setting_key' => 'logo',
                'input_type'  => 'image',
            ],
        ]);

        $payload = $formRequest->payload();

        $this->assertSame('file-uuid-123', $payload['logo']['setting_value']);
        $this->assertSame(
            ['url' => 'https://example.com/logo.png', 'mime_type' => 'image/png'],
            json_decode((string) $payload['logo']['setting_meta'], true),
        );
    }

    public function testPayloadSkipsMalformedSettingEntries(): void
    {
        $request = service('request');
        $request->setGlobal('post', []);

        $formRequest = new SiteIdentityUpdateRequest($request, service('validation'));
        $formRequest->setIdentitySettings([
            ['setting_key' => 'missing_input_type'],
            'not-an-array',
            [
                'setting_key'   => 'valid_setting',
                'input_type'    => 'text',
                'setting_value' => 'Kept',
            ],
        ]);

        $payload = $formRequest->payload();

        $this->assertArrayNotHasKey('missing_input_type', $payload);
        $this->assertSame(['setting_value' => 'Kept'], $payload['valid_setting']);
    }

    public function testValidateAlwaysPasses(): void
    {
        $request = service('request');
        $request->setGlobal('post', []);

        $formRequest = new SiteIdentityUpdateRequest($request, service('validation'));

        $this->assertTrue($formRequest->validate());
        $this->assertSame([], $formRequest->rules());
    }
}
