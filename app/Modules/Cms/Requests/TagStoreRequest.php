<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class TagStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['is_active'];
    }

    public function rules(): array
    {
        return [
            'is_active' => 'permit_empty|in_list[0,1]',
            'translations' => 'permit_empty',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTranslations(): array
    {
        return TranslationRowNormalizer::normalize(
            $this->request->getPost('translations'),
            static function (array $row): bool {
                $name = isset($row['name']) ? trim((string) $row['name']) : '';
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';

                return $name !== '' || $slug !== '';
            },
            static function (array $row): array {
                $name = isset($row['name']) ? trim((string) $row['name']) : '';
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';

                return [
                    'language_id' => (int) ($row['language_id'] ?? 0),
                    'name' => $name,
                    'slug' => $slug,
                ];
            }
        );
    }

    public function payload(): array
    {
        return [
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'translations' => $this->normalizeTranslations(),
        ];
    }
}
