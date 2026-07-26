<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

class CollectionUpdateRequest extends CollectionStoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['collection_key'] = 'required|string|max_length[50]';

        return $rules;
    }
}
