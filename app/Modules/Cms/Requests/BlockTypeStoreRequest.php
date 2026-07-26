<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class BlockTypeStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'block_key',
            'name',
            'description',
            'category',
            'icon',
            'schema_definition',
            'supports_pages',
            'supports_entries',
            'is_container',
            'is_active',
            'sort_order'
        ];
    }

    public function rules(): array
    {
        return [
            'block_key'         => 'required|min_length[2]|max_length[255]',
            'name'              => 'required|min_length[2]|max_length[255]',
            'description'       => 'permit_empty|string',
            'category'          => 'required|min_length[2]|max_length[255]',
            'icon'              => 'permit_empty|string|max_length[255]',
            'schema_definition' => 'required|string',
            'supports_pages'    => 'permit_empty|in_list[0,1]',
            'supports_entries'  => 'permit_empty|in_list[0,1]',
            'is_container'      => 'permit_empty|in_list[0,1]',
            'is_active'         => 'permit_empty|in_list[0,1]',
            'sort_order'        => 'required|integer',
        ];
    }

    public function payload(): array
    {
        $schema = $this->postString('schema_definition');
        $decodedSchema = json_decode($schema, true) ?? [];

        return [
            'block_key'         => $this->postString('block_key'),
            'name'              => $this->postString('name'),
            'description'       => $this->postString('description'),
            'category'          => $this->postString('category'),
            'icon'              => $this->postString('icon'),
            'schema_definition' => $decodedSchema,
            'supports_pages'    => $this->postBool('supports_pages') ? '1' : '0',
            'supports_entries'  => $this->postBool('supports_entries') ? '1' : '0',
            'is_container'      => $this->postBool('is_container') ? '1' : '0',
            'is_active'         => $this->postBool('is_active') ? '1' : '0',
            'sort_order'        => $this->postInt('sort_order'),
        ];
    }
}
