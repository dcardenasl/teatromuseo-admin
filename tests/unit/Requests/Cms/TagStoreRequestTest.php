<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\TagStoreRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class TagStoreRequestTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPayloadDropsBlankOptionalLanguageRows(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'is_active' => '1',
            'translations' => [
                ['language_id' => '1', 'name' => 'Cine', 'slug' => 'cine'],
                ['language_id' => '3', 'name' => '', 'slug' => ''],
                ['language_id' => '5', 'name' => '  Cinéma  ', 'slug' => '  cinema  '],
            ],
        ]);

        $formRequest = new TagStoreRequest($request, service('validation'));

        $this->assertSame([
            'is_active' => '1',
            'translations' => [
                ['language_id' => 1, 'name' => 'Cine', 'slug' => 'cine'],
                ['language_id' => 5, 'name' => 'Cinéma', 'slug' => 'cinema'],
            ],
        ], $formRequest->payload());
    }
}
