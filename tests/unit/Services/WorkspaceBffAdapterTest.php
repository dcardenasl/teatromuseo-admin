<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\BffApiClientInterface;
use App\Modules\Events\Services\EventWorkspaceBffAdapter;
use App\Modules\Iam\Services\RoleWorkspaceBffAdapter;
use App\Modules\Metrics\Services\MetricsWorkspaceBffAdapter;
use App\Modules\Museum\Services\CatalogCollectionItemBffAdapter;
use CodeIgniter\Test\CIUnitTestCase;

final class WorkspaceBffAdapterTest extends CIUnitTestCase
{
    public function testMetricsWorkspaceNormalizesNestedEnvelope(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminMetricsWorkspace')
            ->with('7d')
            ->willReturn($this->successResponse([
                'summary' => ['request_stats' => ['total_requests' => 8]],
                'timeseries' => [['label' => 'today', 'requests' => 8]],
            ]));

        $adapter = new MetricsWorkspaceBffAdapter($bff);

        $this->assertSame([
            'summary' => ['request_stats' => ['total_requests' => 8]],
            'timeseries' => [['label' => 'today', 'requests' => 8]],
        ], $adapter->read('7d'));
        $this->assertFalse($adapter->wasUnavailable());
    }

    public function testRoleWorkspaceNormalizesPermissionSelections(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminIamRoleWorkspace')
            ->with(7)
            ->willReturn($this->successResponse([
                'role' => ['id' => 7, 'code' => 'editor'],
                'allPermissions' => [['id' => 12, 'code' => 'cms.pages.read']],
                'assignedPermissionIds' => [12],
            ]));

        $this->assertSame([
            'role' => ['id' => 7, 'code' => 'editor'],
            'allPermissions' => [['id' => 12, 'code' => 'cms.pages.read']],
            'assignedPermissionIds' => [12],
        ], (new RoleWorkspaceBffAdapter($bff))->read(7));
    }

    public function testCatalogAndEventFailuresRemainAvailableForDirectFallback(): void
    {
        $catalogBff = $this->createMock(BffApiClientInterface::class);
        $catalogBff->expects($this->once())
            ->method('getAdminCatalogCollectionItemWorkspace')
            ->with(9)
            ->willReturn($this->unavailableResponse(503));

        $catalogAdapter = new CatalogCollectionItemBffAdapter($catalogBff);
        $this->assertNull($catalogAdapter->workspace(9));
        $this->assertTrue($catalogAdapter->wasUnavailable());

        $eventBff = $this->createMock(BffApiClientInterface::class);
        $eventBff->expects($this->once())
            ->method('getAdminEventWorkspace')
            ->with(11)
            ->willReturn($this->unavailableResponse(503));

        $eventAdapter = new EventWorkspaceBffAdapter($eventBff);
        $this->assertNull($eventAdapter->workspace(11));
        $this->assertTrue($eventAdapter->wasUnavailable());
    }

    /** @param array<string,mixed> $data */
    private function successResponse(array $data): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => ['data' => $data],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }

    /** @return array<string,mixed> */
    private function unavailableResponse(int $status): array
    {
        return [
            'ok' => false,
            'status' => $status,
            'data' => [],
            'raw' => '',
            'headers' => [],
            'messages' => ['unavailable'],
            'fieldErrors' => [],
        ];
    }
}
