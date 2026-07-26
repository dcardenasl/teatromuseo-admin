<?php

declare(strict_types=1);

if (! function_exists('cms_translation_copy_mappings')) {
    /**
     * Builds the {source, targets} mappings consumed by copyDefaultToAll() for
     * a translatable resource whose fields are posted as
     * translations[{languageIndex}][{field}] — pages, entries, categories,
     * collections, tags, forms, and menus all share this exact shape.
     *
     * @param list<string> $copyFields
     * @param array<int, array<string, mixed>> $languages
     * @return list<array{source: string, targets: list<string>}>
     */
    function cms_translation_copy_mappings(array $copyFields, array $languages, int $defaultLangIndex): array
    {
        $languageCount = count($languages);
        $targetsForField = static function (string $field) use ($languageCount): array {
            if ($languageCount === 0) {
                return [];
            }

            return array_map(
                static fn (int $index): string => '[name="translations[' . $index . '][' . $field . ']"]',
                range(0, $languageCount - 1)
            );
        };

        $mappings = [];
        foreach ($copyFields as $field) {
            $mappings[] = [
                'source' => '[name="translations[' . $defaultLangIndex . '][' . $field . ']"]',
                'targets' => $targetsForField($field),
            ];
        }

        return $mappings;
    }
}
