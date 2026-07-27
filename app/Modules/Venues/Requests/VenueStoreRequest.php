<?php

declare(strict_types=1);

namespace App\Modules\Venues\Requests;

use App\Support\Requests\BaseFormRequest;

class VenueStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['name', 'slug', 'description', 'capacity', 'is_active'];
    }

    public function rules(): array
    {
        return [
            'name' => 'permit_empty|string|max_length[255]',
            'slug' => 'permit_empty|string|max_length[255]',
            'description' => 'permit_empty|string',
            'capacity' => 'permit_empty|integer',
            'is_active' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        return [
            'name' => $this->postString('name'),
            'slug' => $this->postString('slug'),
            'description' => $this->postString('description'),
            'capacity' => $this->postInt('capacity'),
            'is_active' => $this->postString('is_active'),
        ];
    }
}
