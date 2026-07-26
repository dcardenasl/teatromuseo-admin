<?php

declare(strict_types=1);

namespace App\Modules\Cms\Support;

/**
 * Resolves labels, routes, and URLs that vary by block-owner type ('page' vs 'entry').
 *
 * Mostly pure/stateless — extracted from BlockInstanceController, which owns
 * the actual API calls (fetchOwner()) and uses this class for deterministic
 * label/route lookups. previewUrl() is the one exception: for entry owners it
 * does a single targeted fetch of the parent collection (by ID) to resolve
 * localized slugs. Callers must pass in an already-fetched languages list —
 * it never calls languageApiService itself.
 */
class BlockOwnerRouting
{
    public const OWNER_PAGE = 'page';
    public const OWNER_ENTRY = 'entry';

    public static function label(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY
            ? lang('Pages.owner_label_entry')
            : lang('Pages.owner_label_page');
    }

    public static function childLabel(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY
            ? lang('Pages.child_label_subblock')
            : lang('Pages.child_label_slide');
    }

    public static function listRoute(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? route_to('admin.cms.entries') : route_to('admin.cms.pages');
    }

    public static function showRoute(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? 'admin.cms.entries.show' : 'admin.cms.pages.show';
    }

    /**
     * @return array{index:string,create:string,store:string,edit:string,update:string,delete:string,reorder:string,children:string,childrenReorder:string}
     */
    public static function routes(string $ownerType): array
    {
        $prefix = $ownerType === self::OWNER_ENTRY ? 'admin.cms.entries.blocks' : 'admin.cms.pages.blocks';

        return [
            'index'           => $prefix,
            'create'          => $prefix . '.create',
            'store'           => $prefix . '.store',
            'edit'            => $prefix . '.edit',
            'update'          => $prefix . '.update',
            'delete'          => $prefix . '.delete',
            'reorder'         => $prefix . '.reorder',
            'children'        => $prefix . '.children',
            'childrenReorder' => $prefix . '.children.reorder',
        ];
    }

    /**
     * @param array<string, mixed> $owner
     * @param array<int, array<string, mixed>> $languages already-fetched active languages
     *        (e.g. from the caller's cached activeLanguages()) — this method makes no
     *        language API calls of its own.
     */
    public static function previewUrl(string $ownerType, array $owner, array $languages = []): string
    {
        $publicSiteUrl = rtrim((string) env('PUBLIC_SITE_URL'), '/');
        if ($publicSiteUrl === '') {
            return '';
        }

        $languagesMap = [];
        foreach ($languages as $l) {
            if (is_array($l) && isset($l['id'], $l['code'])) {
                $languagesMap[(int) $l['id']] = strtolower((string) $l['code']);
            }
        }
        if ($languagesMap === []) {
            $languagesMap = [1 => 'es', 2 => 'en'];
        }

        if ($ownerType === self::OWNER_PAGE) {
            foreach (($owner['translations'] ?? []) as $translation) {
                if (! is_array($translation)) {
                    continue;
                }

                $slug = trim((string) ($translation['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }

                $langId = (int) ($translation['language_id'] ?? 0);
                $langCode = $languagesMap[$langId] ?? 'es';
                // Domain's PublicPageController::show() receives the literal
                // string 'home' as $slug for the homepage — must match exactly,
                // since page slug resolution is published-only and the
                // signature is what domain checks before bypassing that filter.
                $identifierSlug = $slug === 'home' ? 'home' : $slug;
                $suffix = self::previewQuerySuffix('page', $langCode . ':' . $identifierSlug);

                if ($slug === 'home') {
                    return $publicSiteUrl . '/' . $langCode . $suffix;
                }

                return $publicSiteUrl . '/' . $langCode . '/' . ltrim($slug, '/') . $suffix;
            }
        } elseif ($ownerType === self::OWNER_ENTRY) {
            $ownerId = (int) ($owner['id'] ?? 0);
            $collectionId = $owner['collection_id'] ?? null;
            if ($collectionId === null) {
                return '';
            }

            // Fetch parent collection details to get localized slugs
            $collection = null;
            try {
                $collectionResponse = service('collectionApiService')->get((string) $collectionId);
                if ($collectionResponse['ok'] && is_array($collectionResponse['data'] ?? null)) {
                    $collection = $collectionResponse['data'];
                }
            } catch (\Throwable $e) {
                log_message('error', '[BlockOwnerRouting] Failed to fetch collection ' . $collectionId . ' in previewUrl: ' . $e->getMessage());
            }

            if ($collection === null) {
                return '';
            }

            $collectionSlugs = $collection['localized_slugs'] ?? [];
            if (isset($collection['index_page']['localized_slugs'])) {
                $collectionSlugs = $collection['index_page']['localized_slugs'];
            }

            foreach (($owner['translations'] ?? []) as $translation) {
                if (! is_array($translation)) {
                    continue;
                }

                $slug = trim((string) ($translation['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }

                $langId = (int) ($translation['language_id'] ?? 0);
                $langCode = $languagesMap[$langId] ?? 'es';

                $colSlug = trim((string) ($collectionSlugs[$langCode] ?? $collection['slug'] ?? $collection['collection_key'] ?? ''), '/');
                if ($colSlug !== '') {
                    $colSlug = '/' . $colSlug;
                }

                $suffix = self::previewQuerySuffix('entry', (string) $ownerId);

                return $publicSiteUrl . '/' . $langCode . $colSlug . '/' . ltrim($slug, '/') . $suffix;
            }
        }

        return '';
    }

    /**
     * Builds the signed ?preview=1 query suffix, or '' when no secret is
     * configured — degrading to a plain public link rather than emitting a
     * preview param that domain will simply ignore.
     */
    private static function previewQuerySuffix(string $type, string $identifier): string
    {
        $token = PreviewToken::sign($type, $identifier);
        if ($token === null) {
            return '';
        }

        return '?preview=1&preview_expires=' . $token['expires'] . '&preview_sig=' . $token['sig'];
    }

    public static function notFoundMessage(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY
            ? lang('Pages.owner_not_found_entry')
            : lang('Pages.pages_not_found');
    }
}
