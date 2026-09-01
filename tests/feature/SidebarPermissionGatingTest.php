<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Regression coverage for sidebar section visibility: every top-level
 * section must be gated by has_permission(), matching the permission
 * codes its own routes/controllers actually require. Museum Catalog used
 * to render unconditionally regardless of the signed-in user's permissions.
 *
 * @internal
 */
final class SidebarPermissionGatingTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private function sessionWithPermissions(array $permissions): array
    {
        return [
            'access_token' => 'test-token',
            // /files is used as the authenticated shell route in these tests.
            // The module itself now correctly requires files.read.
            'user'         => ['id' => 1, 'email' => 'user@test.com', 'permissions' => array_values(array_unique([...$permissions, 'files.read']))],
        ];
    }

    public function testFilesSectionHiddenWithoutFilesReadPermission(): void
    {
        $result = $this->withSession([
            'access_token' => 'test-token',
            'user'         => ['id' => 1, 'email' => 'user@test.com', 'permissions' => ['users.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringNotContainsString('>Archivos<', $body);
    }

    public function testFilesSectionVisibleWithFilesReadPermission(): void
    {
        $result = $this->withSession($this->sessionWithPermissions(['users.read']))->get('/files');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringContainsString('>Archivos<', $body);
    }

    /**
     * The layout renders accented labels as HTML entities (e.g. "Catálogo"
     * -> "Cat&aacute;logo"), so decode before asserting on visible text.
     * Route hrefs are pure ASCII and don't need this.
     */
    private function decodedBody(string $body): string
    {
        return html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function testMuseumSectionHiddenWithoutAnyMuseumPermission(): void
    {
        // Mirrors the real "Administrator" role: admin-panel entry + other
        // domains, but none of the catalog.category/technique/collectionItem
        // read permissions the catalog-domain routes actually require.
        $result = $this->withSession($this->sessionWithPermissions(['users.read', 'event.events.read']))->get('/files');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringNotContainsString('Museo (Catálogo)', $body);
        $this->assertStringNotContainsString('admin/museum/collection-items', $body);
        $this->assertStringNotContainsString('admin/museum/categories', $body);
        $this->assertStringNotContainsString('admin/museum/techniques', $body);
    }

    public function testMuseumSectionShowsOnlyItemsMatchingGrantedPermissions(): void
    {
        $result = $this->withSession($this->sessionWithPermissions(['catalog.technique.read']))->get('/files');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringContainsString('Museo (Catálogo)', $body);
        $this->assertStringContainsString('admin/museum/techniques', $body);
        $this->assertStringNotContainsString('admin/museum/collection-items', $body);
        $this->assertStringNotContainsString('admin/museum/categories', $body);
    }

    public function testMuseumSectionShowsAllItemsForSuperadminEquivalentPermissions(): void
    {
        $result = $this->withSession($this->sessionWithPermissions([
            'catalog.collectionItem.read', 'catalog.category.read', 'catalog.technique.read',
        ]))->get('/files');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringContainsString('Museo (Catálogo)', $body);
        $this->assertStringContainsString('admin/museum/collection-items', $body);
        $this->assertStringContainsString('admin/museum/categories', $body);
        $this->assertStringContainsString('admin/museum/techniques', $body);
    }

    public function testMuseumSectionVisibleForSuperadminShortCircuit(): void
    {
        $result = $this->withSession($this->sessionWithPermissions(['iam.superadmin-access']))->get('/files');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringContainsString('Museo (Catálogo)', $body);
        $this->assertStringContainsString('admin/museum/collection-items', $body);
        $this->assertStringContainsString('admin/museum/categories', $body);
        $this->assertStringContainsString('admin/museum/techniques', $body);
    }

    public function testEventsSectionHidesResourcesWithoutReadPermissions(): void
    {
        $result = $this->withSession($this->sessionWithPermissions(['users.read']))->get('/files');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringNotContainsString('admin/events/events', $body);
        $this->assertStringNotContainsString('admin/events/event-types', $body);
        $this->assertStringNotContainsString('admin/venues/venues', $body);
        $this->assertStringNotContainsString('admin/bookings/bookings', $body);
        $this->assertStringNotContainsString('admin/tickets/tickets', $body);
    }

    public function testEventsSectionShowsOnlyResourcesWithReadPermissions(): void
    {
        $result = $this->withSession($this->sessionWithPermissions(['event.venues.read']))->get('/files');

        $result->assertStatus(200);
        $body = $this->decodedBody($result->getBody());
        $this->assertStringContainsString('admin/venues/venues', $body);
        $this->assertStringNotContainsString('admin/events/events', $body);
        $this->assertStringNotContainsString('admin/events/event-types', $body);
        $this->assertStringNotContainsString('admin/bookings/bookings', $body);
        $this->assertStringNotContainsString('admin/tickets/tickets', $body);
    }
}
