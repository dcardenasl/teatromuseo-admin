<?php

declare(strict_types=1);

if (! function_exists('cms_setting_is_translatable')) {
    /**
     * @param array<string, mixed> $setting
     */
    function cms_setting_is_translatable(array $setting): bool
    {
        return ! empty($setting['is_translatable']);
    }
}

if (! function_exists('cms_setting_resolve_label')) {
    /**
     * @param array<string, mixed> $setting
     */
    function cms_setting_resolve_label(array $setting): string
    {
        foreach ($setting['translations'] ?? [] as $translation) {
            if (! empty($translation['label'])) {
                return (string) $translation['label'];
            }
        }

        return (string) ($setting['description'] ?? $setting['setting_key'] ?? '');
    }
}

if (! function_exists('cms_setting_resolve_placeholder')) {
    /**
     * @param array<string, mixed> $setting
     */
    function cms_setting_resolve_placeholder(array $setting): string
    {
        foreach ($setting['translations'] ?? [] as $translation) {
            if (! empty($translation['placeholder'])) {
                return (string) $translation['placeholder'];
            }
        }

        return '';
    }
}

if (! function_exists('cms_setting_resolve_help')) {
    /**
     * @param array<string, mixed> $setting
     */
    function cms_setting_resolve_help(array $setting): string
    {
        foreach ($setting['translations'] ?? [] as $translation) {
            if (! empty($translation['help_text'])) {
                return (string) $translation['help_text'];
            }
        }

        return '';
    }
}

if (! function_exists('cms_setting_translation_value')) {
    /**
     * @param array<string, mixed> $setting
     */
    function cms_setting_translation_value(array $setting, int $languageId, string $field = 'setting_value'): string
    {
        foreach ($setting['translations'] ?? [] as $translation) {
            if ((int) ($translation['language_id'] ?? 0) !== $languageId) {
                continue;
            }

            if (isset($translation[$field])) {
                return (string) $translation[$field];
            }
        }

        return '';
    }
}

if (! function_exists('cms_settings_build_translation_panel')) {
    /**
     * Build normalized translation panel data for CMS settings.
     *
     * @param array<int, array<string, mixed>> $settings
     * @param array<int, array<string, mixed>> $languages
     * @return array{
     *     activeLanguageId: int,
     *     defaultLanguageCode: string,
     *     translationLanguages: array<int, array<string, mixed>>,
     *     rowsByLanguage: array<int, array<int, array<string, mixed>>>,
     *     translateTargetsByLanguageId: array<int, array<int, array<string, mixed>>>,
     *     translateTargets: array<int, array<string, mixed>>,
     *     translatableFieldCount: int
     * }
     */
    function cms_settings_build_translation_panel(array $settings, array $languages, ?int $baseLanguageId): array
    {
        $translationLanguages = [];
        $defaultLanguageCode = '';
        $defaultLang = null;

        foreach ($languages as $language) {
            $languageId = (int) ($language['id'] ?? 0);
            if ($languageId <= 0) {
                continue;
            }

            if ($baseLanguageId !== null && $languageId === $baseLanguageId) {
                $defaultLanguageCode = strtoupper((string) ($language['code'] ?? ''));
                $defaultLang = array_merge($language, ['is_default' => true]);
                continue;
            }

            $translationLanguages[] = array_merge($language, ['is_default' => false]);
        }

        if ($defaultLang !== null) {
            array_unshift($translationLanguages, $defaultLang);
        }

        if ($defaultLanguageCode === '' && ! empty($languages)) {
            $fallbackLanguage = $languages[0] ?? [];
            if (is_array($fallbackLanguage)) {
                $defaultLanguageCode = strtoupper((string) ($fallbackLanguage['code'] ?? ''));
            }
        }

        $translatableSettings = [];
        foreach ($settings as $setting) {
            if (! is_array($setting) || ! cms_setting_is_translatable($setting)) {
                continue;
            }

            $settingKey = trim((string) ($setting['setting_key'] ?? ''));
            if ($settingKey === '') {
                continue;
            }

            $translatableSettings[] = $setting;
        }

        $rowsByLanguage = [];
        $translateTargetsByLanguageId = [];
        $translateTargets = [];
        $activeLanguageId = $baseLanguageId ?? 0;

        foreach ($translationLanguages as $language) {
            $languageId = (int) ($language['id'] ?? 0);
            if ($languageId <= 0) {
                continue;
            }

            if ($activeLanguageId === 0) {
                $activeLanguageId = $languageId;
            }

            $isDefaultLang = !empty($language['is_default']);
            $languageCode = strtoupper((string) ($language['code'] ?? ''));
            $languageLabel = trim((string) ($language['native_name'] ?? $language['name'] ?? $languageCode));
            $rows = [];
            $fieldPairs = [];

            foreach ($translatableSettings as $setting) {
                $settingKey = trim((string) ($setting['setting_key'] ?? ''));
                if ($settingKey === '') {
                    continue;
                }

                $label = cms_setting_resolve_label($setting);
                $inputType = (string) ($setting['input_type'] ?? 'text');

                if ($isDefaultLang) {
                    $fieldName = sprintf('%s_value', $settingKey);
                    $value = (string) ($setting['setting_value'] ?? '');
                    $placeholder = $label;
                } else {
                    $fieldName = sprintf('%s_translations[%d]', $settingKey, $languageId);
                    $value = cms_setting_translation_value($setting, $languageId);
                    $placeholder = $label !== '' ? sprintf('%s (%s)', $label, strtolower($languageCode)) : strtolower($languageCode);
                }

                $rows[] = [
                    'key' => $settingKey,
                    'name' => $fieldName,
                    'id' => str_replace(['[', ']'], '_', $fieldName),
                    'label' => $label,
                    'help' => cms_setting_resolve_help($setting),
                    'value' => $value,
                    'placeholder' => $placeholder,
                    'inputType' => $inputType,
                    'readonly' => ! empty($setting['is_readonly']),
                    'languageCode' => $languageCode,
                    'languageLabel' => $languageLabel,
                ];

                if (!$isDefaultLang) {
                    $fieldPairs[] = [
                        'from' => sprintf('[name="%s_value"]', $settingKey),
                        'to'   => sprintf('[name="%s"]', $fieldName),
                    ];
                }
            }

            if ($rows !== []) {
                $rowsByLanguage[$languageId] = $rows;
                if (!$isDefaultLang) {
                    $translateTargetsByLanguageId[$languageId] = $fieldPairs;
                    $translateTargets[] = [
                        'langCode' => $languageCode,
                        'fieldPairs' => $fieldPairs,
                    ];
                }
            }
        }

        return [
            'activeLanguageId' => $activeLanguageId,
            'defaultLanguageCode' => $defaultLanguageCode,
            'translationLanguages' => $translationLanguages,
            'rowsByLanguage' => $rowsByLanguage,
            'translateTargetsByLanguageId' => $translateTargetsByLanguageId,
            'translateTargets' => $translateTargets,
            'translatableFieldCount' => count($translatableSettings),
        ];
    }
}
