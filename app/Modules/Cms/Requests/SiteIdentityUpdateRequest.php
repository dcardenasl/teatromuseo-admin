<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

/**
 * Metadata-driven request: builds update payloads for any number of settings
 * in the 'identity' group without hard-coded field names.
 *
 * Each setting is passed in as an element of $identitySettings (the array returned
 * by SettingApiService::getByGroup('identity')). The setting's input_type determines
 * how the form field is encoded (file picker vs translatable text vs plain value).
 */
class SiteIdentityUpdateRequest extends BaseFormRequest
{
    /** @var array<mixed> */
    private array $identitySettings = [];

    /** @var array<mixed> */
    private array $languages = [];

    private ?int $baseLanguageId = null;

    /** @param array<mixed> $settings */
    public function setIdentitySettings(array $settings): void
    {
        $this->identitySettings = $settings;
    }

    /** @param array<mixed> $languages */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }

    public function setBaseLanguageId(?int $languageId): void
    {
        $this->baseLanguageId = $languageId;
    }

    protected function fields(): array
    {
        return [];
    }

    public function rules(): array
    {
        return [];
    }

    public function validate(): bool
    {
        // This request is metadata-driven, so the update gate should not
        // depend on fixed field definitions.
        return true;
    }

    /**
     * Returns a map of setting_key → update payload for the API.
     *
     * @return array<string, array<string, mixed>>
     */
    public function payload(): array
    {
        $result = [];

        foreach ($this->identitySettings as $setting) {
            if (!is_array($setting) || !isset($setting['setting_key'], $setting['input_type'])) {
                continue;
            }

            $key       = (string) $setting['setting_key'];
            $inputType = (string) $setting['input_type'];

            $result[$key] = $this->buildSettingPayload($key, $inputType, $setting);
        }

        return $result;
    }

    /**
     * Build the update payload for a single setting based on its input_type.
     *
     * @param array<string, mixed> $setting
     * @return array<string, mixed>
     */
    private function buildSettingPayload(string $key, string $inputType, array $setting): array
    {
        $isTranslatable = !empty($setting['is_translatable']);

        if (in_array($inputType, ['image', 'file'], true)) {
            return $this->buildFilePayload($key);
        }

        $field = "{$key}_value";
        $postedValue = $this->request->getPost($field);
        // Never convert an omitted metadata-driven control into an empty
        // overwrite. Preserve the canonical value returned by the API.
        $value = is_scalar($postedValue)
            ? (string) $postedValue
            : (string) ($setting['setting_value'] ?? '');

        $payload = ['setting_value' => $value];

        if ($isTranslatable && !empty($this->languages)) {
            $translations = $this->buildTranslations($key);
            if (!empty($translations)) {
                $payload['translations'] = $translations;
            }
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function buildFilePayload(string $key): array
    {
        return [
            'setting_value' => $this->postString("{$key}_file_id"),
            'setting_meta'  => json_encode([
                'url'       => $this->postString("{$key}_url"),
                'mime_type' => $this->postString("{$key}_mime_type"),
            ]) ?: null,
        ];
    }

    /**
     * @return array<array{language_id: int, setting_value: string}>
     */
    private function buildTranslations(string $key): array
    {
        $postTranslations = $this->request->getPost("{$key}_translations");
        if (!is_array($postTranslations)) {
            return [];
        }

        $baseId = $this->baseLanguageId;
        $translations = [];

        foreach ($this->languages as $lang) {
            $langId = (int) ($lang['id'] ?? 0);
            if ($langId <= 0 || $langId === $baseId) {
                continue;
            }

            if (!array_key_exists($langId, $postTranslations)) {
                continue;
            }

            $val = (string) $postTranslations[$langId];
            if ($val === '') {
                continue;
            }

            $translations[] = [
                'language_id'   => $langId,
                'setting_value' => $val,
            ];
        }

        return $translations;
    }
}
