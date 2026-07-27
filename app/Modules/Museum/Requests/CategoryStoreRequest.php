<?php

declare(strict_types=1);

namespace App\Modules\Museum\Requests;

use App\Support\Requests\BaseFormRequest;

class CategoryStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['name', 'slug', 'icon', 'short_description', 'sort_order'];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[255]',
            'slug' => 'required|min_length[2]|max_length[255]',
            'icon' => 'permit_empty|string|max_length[255]',
            'short_description' => 'permit_empty|string',
            'sort_order' => 'permit_empty|integer',
        ];
    }

    public function payload(): array
    {
        return [
            'name' => $this->postString('name'),
            'slug' => $this->postString('slug'),
            'icon' => $this->postString('icon'),
            'short_description' => $this->postString('short_description'),
            'sort_order' => $this->postInt('sort_order', 0),
        ];
    }
}
