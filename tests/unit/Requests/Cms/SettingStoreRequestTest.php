<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\SettingStoreRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class SettingStoreRequestTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPayloadPrefersCanonicalSettingValueWhenProvided(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'setting_key' => 'site.title',
            'setting_type' => 'string',
            'setting_value' => 'New title',
            'setting_value_string' => 'Legacy title',
            'setting_group' => 'general',
            'is_translatable' => '1',
            'sort_order' => '3',
            'description' => 'Header title',
            'translations' => [
                2 => 'Translated title',
            ],
        ]);

        $formRequest = new SettingStoreRequest($request, service('validation'));
        $formRequest->setLanguages([
            ['id' => 1, 'code' => 'es', 'is_default' => 1],
            ['id' => 2, 'code' => 'en', 'is_default' => 0],
        ]);
        $formRequest->setBaseLanguageId(1);
        $payload = $formRequest->payload();

        $this->assertSame('New title', $payload['setting_value']);
        $this->assertSame('1', $payload['is_translatable']);
        $this->assertSame(3, $payload['sort_order']);
        $this->assertSame([
            [
                'language_id' => 2,
                'setting_value' => 'Translated title',
            ],
        ], $payload['translations']);
    }
}
