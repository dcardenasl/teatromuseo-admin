<?php

declare(strict_types=1);

namespace App\Modules\Cms\Support;

/**
 * Signs preview links consumed by ci4-website-builder-domain's PreviewToken::verify().
 * Shares CMS_PREVIEW_SECRET with that repo — must stay in sync.
 *
 * Returns null (no signature) when the secret is not configured, so callers
 * degrade to a plain public link instead of emitting a preview=1 that domain
 * will just ignore.
 */
class PreviewToken
{
    /**
     * $identifier must match exactly what domain's PreviewToken::verify() checks
     * against for that $type — an entry ID (as a string), or "{lang}:{slug}" for
     * pages (page slug resolution is published-only, so domain has no page ID to
     * bind to until it decides whether to bypass that filter).
     *
     * @return array{expires: int, sig: string}|null
     */
    public static function sign(string $type, string $identifier, int $ttlSeconds = 3600): ?array
    {
        $secret = (string) env('CMS_PREVIEW_SECRET', '');
        if ($secret === '' || $identifier === '') {
            return null;
        }

        $expires = time() + $ttlSeconds;

        return [
            'expires' => $expires,
            'sig'     => hash_hmac('sha256', $type . ':' . $identifier . ':' . $expires, $secret),
        ];
    }
}
