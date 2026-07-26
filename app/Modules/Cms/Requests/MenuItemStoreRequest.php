<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class MenuItemStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'menu_id',
            'parent_id',
            'link_type',
            'page_id',
            'entry_id',
            'collection_id',
            'link_target',
            'icon',
            'css_class',
            'sort_order',
            'is_active',
            'translations',
        ];
    }

    public function rules(): array
    {
        return [
            'menu_id'     => 'required|integer',
            'parent_id'   => 'permit_empty|integer',
            'link_type'   => 'required|in_list[page,entry,collection_listing,custom_url,no_link]',
            'page_id'     => 'permit_empty|integer',
            'entry_id'    => 'permit_empty|integer',
            'collection_id' => 'permit_empty|integer',
            'link_target' => 'required|in_list[_self,_blank]',
            'icon'        => 'permit_empty|string|max_length[50]',
            'css_class'   => 'permit_empty|string|max_length[100]',
            'sort_order'  => 'required|integer',
            'is_active'   => 'permit_empty|in_list[0,1]',
        ];
    }

    public function payload(): array
    {
        $payload = [
            'menu_id'       => $this->postInt('menu_id'),
            'parent_id'     => is_numeric($this->request->getPost('parent_id')) ? (int) $this->request->getPost('parent_id') : null,
            'link_type'     => $this->postString('link_type'),
            'page_id'       => $this->postInt('page_id'),
            'entry_id'      => $this->postInt('entry_id'),
            'collection_id' => $this->postInt('collection_id'),
            'link_target'   => $this->postString('link_target') ?: '_self',
            'icon'          => $this->postString('icon') ?: null,
            'css_class'     => $this->postString('css_class') ?: null,
            'sort_order'    => $this->postInt('sort_order'),
            'is_active'     => $this->postBool('is_active') ? '1' : '0',
            'translations'  => [],
        ];

        if ($payload['link_type'] !== 'page') {
            $payload['page_id'] = null;
        }
        if ($payload['link_type'] !== 'entry') {
            $payload['entry_id'] = null;
        }
        if ($payload['link_type'] !== 'collection_listing') {
            $payload['collection_id'] = null;
        }

        $payload['translations'] = TranslationRowNormalizer::normalize(
            $this->postArray('translations'),
            static function (array $row): bool {
                $label = isset($row['label']) ? trim((string) $row['label']) : '';
                $customUrl = isset($row['custom_url']) ? trim((string) $row['custom_url']) : '';

                return $label !== '' || $customUrl !== '';
            },
            function (array $row, int|string $languageId) use ($payload): array {
                $label = isset($row['label']) ? trim((string) $row['label']) : '';
                $customUrl = isset($row['custom_url']) ? trim((string) $row['custom_url']) : '';

                return [
                    'language_id' => (int) $languageId,
                    'label'       => $label,
                    'custom_url'  => $payload['link_type'] === 'custom_url' ? ($customUrl !== '' ? $customUrl : null) : null,
                ];
            }
        );

        return $payload;
    }
}
