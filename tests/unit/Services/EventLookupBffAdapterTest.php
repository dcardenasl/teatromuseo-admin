<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\BffApiClientInterface;
use App\Modules\Events\Services\EventLookupBffAdapter;
use CodeIgniter\Test\CIUnitTestCase;

final class EventLookupBffAdapterTest extends CIUnitTestCase
{
    public function testNormalizesBffAggregateIntoSectionsAndSource(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminEventLookups')
            ->with('occurrence')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'status' => 'success',
                    'data' => [
                        'context' => 'occurrence',
                        'source' => ['event' => 'ok', 'state' => 'ok'],
                        'sections' => [
                            'events' => [['id' => 1, 'title' => 'Opening night']],
                            'venues' => [['id' => 2, 'name' => 'Main hall']],
                        ],
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        $adapter = new EventLookupBffAdapter($bff);
        $result  = $adapter->read('occurrence');

        $this->assertTrue($adapter->sourceAvailable($result));
        $this->assertSame([['id' => 1, 'title' => 'Opening night']], $adapter->section($result, 'events'));
        $this->assertSame([['id' => 2, 'name' => 'Main hall']], $adapter->section($result, 'venues'));
    }

    public function testUnavailableBffResponseProducesUnavailableEmptySections(): void
    {
        $response = [
            'ok' => false,
            'status' => 503,
            'data' => [],
            'raw' => '',
            'headers' => [],
            'messages' => ['unavailable'],
            'fieldErrors' => [],
        ];
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->method('getAdminEventLookups')->willReturn($response);

        $adapter = new EventLookupBffAdapter($bff);
        $result  = $adapter->read('occurrence');

        $this->assertFalse($adapter->sourceAvailable($result));
        $this->assertSame([], $adapter->section($result, 'events'));
    }
}
