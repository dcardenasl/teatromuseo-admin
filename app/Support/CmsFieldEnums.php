<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A local mirror of the two `App\Libraries\Cms\CmsEnums` constants this app
 * actually reads (`MENU_LINK_TYPES`, `NON_TRANSLATABLE_TYPES`), not the
 * domain's full enum set (`WORKFLOW_STATUS`, `PAGE_STATUS`, `PAGE_TYPE`,
 * `PAGE_TEMPLATE_TYPES`, `SITEMAP_CHANGEFREQ`), which this app never touches.
 *
 * Deliberately owned here, not imported: this app used to resolve
 * `App\Libraries\Cms\CmsEnums` through a `composer.json` PSR-4 mapping into
 * `teatromuseo-cms-domain`'s own `app/Libraries/Cms/` directory — a relative
 * path that only resolves when both repos sit side by side in the exact
 * monorepo layout. It broke CI (the checkout step cloned the sibling repo to
 * a path this app's mapping didn't reference) and Docker (the image only
 * copies this app's own `app/`, so it never contained the class at all).
 * A small, explicitly-owned copy of just what's used is more honest than a
 * cross-repo path that silently assumes a directory layout no build actually
 * guarantees.
 *
 * Keep in sync with `teatromuseo-cms-domain/app/Libraries/Cms/CmsEnums.php`
 * by hand when either list changes — same trade-off any decoupled
 * frontend/backend pair makes for client-side validation of enum values the
 * backend also enforces.
 */
final class CmsFieldEnums
{
    public const MENU_LINK_TYPES = ['page', 'entry', 'collection_listing', 'event_listing', 'custom_url', 'no_link'];

    public const NON_TRANSLATABLE_TYPES = ['media_reference', 'repeater', 'boolean', 'integer', 'select', 'number'];

    /** @param array<string> $values */
    public static function inListRule(array $values): string
    {
        return 'in_list[' . implode(',', $values) . ']';
    }
}
