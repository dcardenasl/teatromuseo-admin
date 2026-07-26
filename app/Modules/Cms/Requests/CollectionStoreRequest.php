<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class CollectionStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'collection_type',
            'collection_key',
            'default_language_id',
            'default_sitemap_priority',
            'default_changefreq',
            'sort_order',
            'is_active',
            'requires_approval',
            'enables_categories',
            'enables_tags',
            'block_template',
            'wizard_config',
        ];
    }

    public function data(): array
    {
        $data = parent::data();
        $data['translations'] = $this->postArray('translations');

        return $data;
    }

    public function rules(): array
    {
        return [
            'collection_type' => 'permit_empty|string|max_length[50]|regex_match[/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/]',
            'collection_key' => 'permit_empty|string|max_length[50]',
            'default_language_id' => 'permit_empty|integer',
            'default_sitemap_priority' => 'permit_empty|decimal',
            'default_changefreq' => 'permit_empty|in_list[always,hourly,daily,weekly,monthly,yearly,never]',
            'sort_order' => 'permit_empty|integer',
            'is_active' => 'permit_empty|in_list[0,1]',
            'requires_approval' => 'permit_empty|in_list[0,1]',
            'enables_categories' => 'permit_empty|in_list[0,1]',
            'enables_tags' => 'permit_empty|in_list[0,1]',
            'block_template' => 'permit_empty',
            'wizard_config' => 'permit_empty',
            'translations' => 'permit_empty',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTranslations(): array
    {
        return TranslationRowNormalizer::normalize(
            $this->postArray('translations'),
            static function (array $row): bool {
                $slug = isset($row['slug']) ? trim((string) $row['slug'], " \t\n\r\0\x0B/") : '';
                $name = isset($row['name']) ? trim((string) $row['name']) : '';
                $description = isset($row['description']) ? trim((string) $row['description']) : '';

                return $slug !== '' || $name !== '' || $description !== '';
            },
            static function (array $row): array {
                $slug = isset($row['slug']) ? trim((string) $row['slug'], " \t\n\r\0\x0B/") : '';
                $name = isset($row['name']) ? trim((string) $row['name']) : '';
                $description = isset($row['description']) ? trim((string) $row['description']) : '';
                $entryCtaLabel = isset($row['entry_cta_label']) ? trim((string) $row['entry_cta_label']) : '';

                return [
                    'language_id' => (int) ($row['language_id'] ?? 0),
                    'slug' => $slug !== '' ? $slug : null,
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'entry_cta_label' => $entryCtaLabel !== '' ? $entryCtaLabel : null,
                ];
            }
        );
    }

    public function payload(): array
    {
        $wizardConfig = $this->postString('wizard_config');
        $resolvedType = 'other';
        if ($wizardConfig) {
            $decoded = json_decode($wizardConfig, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($decoded['type'])) {
                $resolvedType = (string) $decoded['type'];
            }
        }

        $passedType = $this->postString('collection_type');
        $collectionType = ($passedType && $passedType !== 'other') ? $passedType : $resolvedType;
        $translations = $this->normalizeTranslations();
        $collectionKey = trim($this->postString('collection_key'));

        if ($collectionKey === '') {
            $collectionKey = $this->resolveCollectionKey($translations, $this->postInt('default_language_id', 0));
        }

        $payload = [
            'collection_type' => $collectionType,
            'collection_key' => $collectionKey,
            'default_sitemap_priority' => $this->postString('default_sitemap_priority') ?: '0.5',
            'default_changefreq' => $this->postString('default_changefreq') ?: 'weekly',
            'sort_order' => $this->postInt('sort_order', 0),
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'requires_approval' => $this->postBool('requires_approval') ? '1' : '0',
            'enables_categories' => $this->postBool('enables_categories') ? '1' : '0',
            'enables_tags' => $this->postBool('enables_tags') ? '1' : '0',
            'block_template' => $this->postString('block_template'),
            'wizard_config' => $this->postString('wizard_config'),
            'translations' => $translations,
        ];

        return $payload;
    }

    /**
     * @param array<int, array<string, mixed>> $translations
     */
    private function resolveCollectionKey(array $translations, int $defaultLanguageId): string
    {
        if ($defaultLanguageId > 0) {
            foreach ($translations as $translation) {
                if ((int) ($translation['language_id'] ?? 0) !== $defaultLanguageId) {
                    continue;
                }

                $name = trim((string) ($translation['name'] ?? ''));
                if ($name !== '') {
                    return $this->normalizeCollectionKeyValue($name);
                }

                $slug = trim((string) ($translation['slug'] ?? ''));
                if ($slug !== '') {
                    return $this->normalizeCollectionKeyValue($slug);
                }
            }
        }

        foreach ($translations as $translation) {
            $name = trim((string) ($translation['name'] ?? ''));
            if ($name !== '') {
                return $this->normalizeCollectionKeyValue($name);
            }

            $slug = trim((string) ($translation['slug'] ?? ''));
            if ($slug !== '') {
                return $this->normalizeCollectionKeyValue($slug);
            }
        }

        return '';
    }

    private function normalizeCollectionKeyValue(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? $normalized;
        $normalized = preg_replace('/-{2,}/', '-', $normalized) ?? $normalized;
        $normalized = trim($normalized, '-');

        return substr($normalized, 0, 50);
    }
}
