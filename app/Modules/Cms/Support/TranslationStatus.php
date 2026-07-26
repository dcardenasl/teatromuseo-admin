<?php

declare(strict_types=1);

namespace App\Modules\Cms\Support;

/**
 * Evaluates translation coverage for one active language.
 *
 * Resources differ in whether the default language's content lives on the
 * resource's own canonical fields (Settings' `setting_value`) or purely in a
 * translation row like every other language (Pages, Collections, Menus,
 * Forms all have no top-level `title`/`name` field at all — confirmed
 * against their response DTOs). Rather than assume one model per view, a
 * real translation row always wins when present; canonical `_source` fields
 * are only consulted to answer "was there no row at all" or to fill in a
 * field the row itself left blank. This is what actually keeps a fully
 * translated default language from being flagged as missing regardless of
 * which shape the resource happens to use.
 */
final class TranslationStatus
{
    /**
     * @param array<string, mixed> $language
     * @param array<int, array<string, mixed>> $translations
     * @param list<string> $requiredFields
     * @return array{status: string, completed_fields: list<string>, missing_fields: list<string>}
     */
    public static function evaluate(
        array $language,
        array $translations,
        array $requiredFields,
        mixed $sourceUpdatedAt = null,
    ): array {
        $isDefault = ! empty($language['is_default']);
        $languageId = (int) ($language['id'] ?? 0);
        $translation = null;
        foreach ($translations as $candidate) {
            if ((int) ($candidate['language_id'] ?? 0) === $languageId) {
                $translation = $candidate;
                break;
            }
        }

        if ($translation === null) {
            if ($isDefault) {
                return self::fieldsResult($requiredFields, is_array($language['_source'] ?? null) ? $language['_source'] : []);
            }

            return [
                'status' => 'missing',
                'completed_fields' => [],
                'missing_fields' => array_values($requiredFields),
            ];
        }

        $values = $translation;
        if ($isDefault) {
            $source = is_array($language['_source'] ?? null) ? $language['_source'] : [];
            foreach ($requiredFields as $field) {
                if (trim((string) ($values[$field] ?? '')) === '' && trim((string) ($source[$field] ?? '')) !== '') {
                    $values[$field] = $source[$field];
                }
            }
        }

        $result = self::fieldsResult($requiredFields, $values);
        if ($result['status'] === 'missing') {
            $result['status'] = 'incomplete';
        }
        if ($result['status'] !== 'complete') {
            return $result;
        }

        $sourceTimestamp = strtotime((string) $sourceUpdatedAt);
        $translationTimestamp = strtotime((string) ($translation['updated_at'] ?? ''));
        if ($sourceTimestamp !== false && $translationTimestamp !== false && $translationTimestamp < $sourceTimestamp) {
            $result['status'] = 'outdated';
        }

        return $result;
    }

    /**
     * @param list<string> $requiredFields
     * @param array<string, mixed> $values
     * @return array{status: string, completed_fields: list<string>, missing_fields: list<string>}
     */
    private static function fieldsResult(array $requiredFields, array $values): array
    {
        $completed = [];
        $missing = [];
        foreach ($requiredFields as $field) {
            if (trim((string) ($values[$field] ?? '')) === '') {
                $missing[] = $field;
            } else {
                $completed[] = $field;
            }
        }

        return [
            'status' => $missing === [] ? 'complete' : ($completed === [] ? 'missing' : 'incomplete'),
            'completed_fields' => $completed,
            'missing_fields' => $missing,
        ];
    }

    /**
     * Single source of truth for the status -> Tailwind classes mapping, so no
     * view has to repeat the missing/outdated/incomplete/complete ternary chain.
     *
     * 'action' is the border+bg+text style used by the "translations pending"
     * banner (only ever renders non-complete statuses); 'pill' is the compact
     * bg+text badge used on detail cards and the menu tree, which also renders
     * 'complete'; 'dot' is a solid-color w-1.5/h-1.5 background class (no
     * text) for tight spaces like a language-tab indicator.
     */
    public static function badgeClasses(string $status, string $variant = 'pill'): string
    {
        if ($variant === 'action') {
            return match ($status) {
                'missing' => 'border-red-200 bg-red-50 text-red-800',
                'outdated' => 'border-orange-200 bg-orange-50 text-orange-800',
                default => 'border-amber-200 bg-amber-50 text-amber-800',
            };
        }

        if ($variant === 'dot') {
            return match ($status) {
                'missing' => 'bg-red-500',
                'outdated' => 'bg-orange-500',
                'incomplete' => 'bg-amber-500',
                default => 'bg-green-500',
            };
        }

        return match ($status) {
            'missing' => 'bg-red-100 text-red-700',
            'outdated' => 'bg-orange-100 text-orange-700',
            'incomplete' => 'bg-amber-100 text-amber-700',
            default => 'bg-green-100 text-green-700',
        };
    }

    /**
     * Appends focus_lang to an edit URL, honoring an existing query string
     * instead of assuming the base URL is always bare (mirrors
     * remoteTable.js's translationEditUrl()).
     */
    public static function editUrl(string $baseUrl, int $languageId): string
    {
        if ($languageId <= 0) {
            return $baseUrl;
        }

        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl . $separator . 'focus_lang=' . $languageId;
    }
}
