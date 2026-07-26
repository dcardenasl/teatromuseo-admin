<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\MenuStoreRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class MenuStoreRequestTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPayloadNormalizesDynamicMenuTranslations(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'menu_key' => 'legal',
            'location' => 'footer',
            'is_active' => '1',
            'translations' => [
                ['language_id' => '1', 'name' => 'Menú legal'],
                ['language_id' => '3', 'name' => 'Menu légal'],
                ['language_id' => '4', 'name' => ''],
                ['language_id' => '5', 'name' => '  Menu legale  '],
            ],
        ]);

        $formRequest = new MenuStoreRequest($request, service('validation'));

        $this->assertSame([
            'menu_key' => 'legal',
            'location' => 'footer',
            'is_active' => '1',
            'translations' => [
                ['language_id' => 1, 'name' => 'Menú legal'],
                ['language_id' => 3, 'name' => 'Menu légal'],
                ['language_id' => 5, 'name' => 'Menu legale'],
            ],
        ], $formRequest->payload());
    }
}
