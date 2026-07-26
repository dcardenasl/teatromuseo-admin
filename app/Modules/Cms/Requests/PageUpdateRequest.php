<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

class PageUpdateRequest extends PageStoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['page_type'] = 'permit_empty|in_list[' . implode(',', \App\Modules\Cms\Support\CmsPresetCatalog::pageTypes()) . ']';

        return $rules;
    }

    public function payload(): array
    {
        $payload = parent::payload();
        unset($payload['sort_order']);

        if ($this->postString('page_type') === '') {
            unset($payload['page_type']);
        }

        return $payload;
    }
}
