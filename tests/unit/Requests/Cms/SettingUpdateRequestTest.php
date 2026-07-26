<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\SettingUpdateRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class SettingUpdateRequestTest extends CIUnitTestCase
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
            'setting_value' => 'Updated title',
            'setting_value_string' => 'Legacy title',
            'setting_group' => 'general',
            'is_translatable' => '0',
            'description' => 'Header title',
        ]);

        $formRequest = new SettingUpdateRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertSame('Updated title', $payload['setting_value']);
        $this->assertArrayNotHasKey('sort_order', $payload);
        $this->assertArrayNotHasKey('translations', $payload);
    }
}
