<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * `admin/cms/translate` is a shared utility consumed by content forms across
 * CMS, Museum and Events. A single `permission:<code>` route filter can't
 * express "any read-or-write permission belonging to one of those content
 * modules", so TranslateController::translate() enforces an OR-of-permissions
 * check in-code — this regression guards that a session holding only an
 * unrelated broad-admin-section permission (e.g. `iam.admin-access`, which
 * grants entry to `/admin/cms/*` via `AdminFilter` but has nothing to do
 * with content) can no longer reach the external-provider proxy, while a
 * genuine content permission (even read-only, matching the CMS Wizard's
 * `cms.entries.read`-is-sufficient design) still can.
 *
 * @internal
 */
final class TranslateFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testTranslateDeniesSessionWithOnlyAnUnrelatedAdminPermission(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['iam.admin-access']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/translate?text=hola&target_lang=en');

        $result->assertStatus(403);
    }

    public function testTranslateAllowsSessionWithAContentReadPermission(): void
    {
        // cms.tags.read: both grants entry through AdminFilter's broad
        // `/admin/cms/*` section gate (it's in Config\AdminAccess::$permissions)
        // and satisfies TranslateController's own content-permission check —
        // matches the pre-existing WizardFlowTest::testTranslateProxyDoesNotRequirePageReadPermission
        // expectation that a read-only content permission is sufficient.
        // No text/target_lang query params: if the permission gate is passed,
        // the controller must fail on parameter validation (400) rather than
        // on authorization (403) or by reaching the real Google Translate API.
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.tags.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/translate');

        $result->assertStatus(400);
    }
}
