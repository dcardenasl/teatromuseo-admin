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

    public function testValidateAcceptsMetadataDrivenIdentityPayloads(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'site_logo_file_id'   => '20',
            'site_logo_url'       => 'http://localhost:8180/uploads/2026/06/28/logo_md.gif',
            'site_logo_mime_type' => 'image/gif',
        ]);

        $formRequest = new SiteIdentityUpdateRequest($request, service('validation'));
        $formRequest->setIdentitySettings([
            [
                'setting_key' => 'site_logo',
                'input_type'  => 'image',
            ],
        ]);

        $this->assertTrue($formRequest->validate());
    }
}
