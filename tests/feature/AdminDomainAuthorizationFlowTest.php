<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Regression coverage for the Admin domain modules that use declarative,
 * route-level permissions. A permission from another domain must not grant
 * access, and a read permission must not be promoted to a mutation.
 *
 * @internal
 */
final class AdminDomainAuthorizationFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /** @var list<string> */
    private const DOMAIN_INDEX_ROUTES = [
        '/admin/bookings/bookings',
        '/admin/eventreferences/event-references',
        '/admin/events/events',
        '/admin/events/event-types',
        '/admin/occurrences/occurrences',
        '/admin/tickettypes/ticket-types',
        '/admin/tickets/tickets',
        '/admin/venues/venues',
        '/admin/museum/categories',
        '/admin/museum/techniques',
        '/admin/museum/collection-items',
    ];

    /** @var list<array{method: 'get'|'post', path: string, permissions: list<string>}> */
    private const INSUFFICIENT_PERMISSIONS = [
        [
            'method'      => 'get',
            'path'        => '/admin/events/events/create',
            'permissions' => ['event.events.read'],
        ],
        [
            'method'      => 'post',
            'path'        => '/admin/events/events',
            'permissions' => ['event.events.read'],
        ],
        [
            'method'      => 'post',
            'path'        => '/admin/events/events/test-uuid/delete',
            'permissions' => ['event.events.write'],
        ],
        [
            'method'      => 'get',
            'path'        => '/admin/museum/categories/create',
            'permissions' => ['catalog.category.read'],
        ],
        [
            'method'      => 'post',
            'path'        => '/admin/museum/categories',
            'permissions' => ['catalog.category.read'],
        ],
        [
            'method'      => 'post',
            'path'        => '/admin/museum/categories/test-uuid/delete',
            'permissions' => ['catalog.category.update'],
        ],
        [
            // Regression for the route-shadowing bug: `categories/reorder` was
            // being captured by the dynamic `categories/(:segment)` -> show()
            // route (registered first), so it silently ran under
            // `catalog.category.read` instead of `.update`.
            'method'      => 'get',
            'path'        => '/admin/museum/categories/reorder',
            'permissions' => ['catalog.category.read'],
        ],
        [
            'method'      => 'get',
            'path'        => '/admin/museum/techniques/reorder',
            'permissions' => ['catalog.technique.read'],
        ],
    ];

    public function testUnrelatedPermissionCannotEnterDomainModules(): void
    {
        foreach (self::DOMAIN_INDEX_ROUTES as $path) {
            $result = $this->withSession([
                'access_token' => 'token',
                'user'         => ['permissions' => ['cms.tags.read']],
                'permissions_refreshed_at' => time(),
            ])->get($path);

            $result->assertRedirectTo(site_url('dashboard'));
        }
    }

    public function testInsufficientPermissionCannotExecuteMutation(): void
    {
        foreach (self::INSUFFICIENT_PERMISSIONS as $case) {
            $request = $this->withSession([
                'access_token' => 'token',
                'user'         => ['permissions' => $case['permissions']],
                'permissions_refreshed_at' => time(),
            ]);

            $result = $case['method'] === 'get'
                ? $request->get($case['path'])
                : $request->post($case['path'], [csrf_token() => csrf_hash()]);

            $result->assertRedirectTo(site_url('dashboard'));
        }
    }
}
