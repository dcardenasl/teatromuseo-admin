<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class MenuStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['menu_key', 'location', 'is_active', 'translations'];
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
            'menu_key' => 'required|min_length[2]|max_length[255]',
            'location' => 'required|min_length[2]|max_length[50]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'translations' => 'permit_empty',
            'translations.*.language_id' => 'permit_empty|integer',
            'translations.*.name' => 'permit_empty|string|max_length[150]',
        ];
    }

    public function payload(): array
    {
        return [
            'menu_key' => $this->postString('menu_key'),
            'location' => $this->postString('location'),
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'translations' => TranslationRowNormalizer::normalize(
                $this->postArray('translations'),
                static fn (array $row): bool => trim((string) ($row['name'] ?? '')) !== '',
                static fn (array $row): array => [
                    'language_id' => (int) ($row['language_id'] ?? 0),
                    'name' => trim((string) ($row['name'] ?? '')),
                ],
            ),
        ];
    }
}
