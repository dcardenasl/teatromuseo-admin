<?php

declare(strict_types=1);

namespace App\Modules\Museum\Requests;

use App\Support\Requests\BaseFormRequest;

class CollectionItemStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['name', 'category_id', 'inventory_code', 'status', 'summary', 'curiosidad', 'contenido', 'origin', 'period', 'creator', 'ubicacion', 'materials', 'cover_file_id', 'gallery_file_ids', 'show_in_totem', 'internal_notes', 'collection_number', 'collection_group', 'physical_description', 'dimensions', 'ingress_type', 'donated_by', 'tags', 'links', 'company_history', 'is_active'];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[255]',
            'category_id' => 'required',
            'inventory_code' => 'required|min_length[2]|max_length[255]',
            'status' => 'required|min_length[2]|max_length[255]',
            'summary' => 'permit_empty|string',
            'curiosidad' => 'permit_empty|string',
            'contenido' => 'permit_empty|string',
            'origin' => 'permit_empty|string|max_length[255]',
            'period' => 'permit_empty|string|max_length[255]',
            'creator' => 'permit_empty|string|max_length[255]',
            'ubicacion' => 'permit_empty|string|max_length[255]',
            'materials' => 'permit_empty|string',
            'cover_file_id' => 'permit_empty|integer',
            'gallery_file_ids' => 'permit_empty|string',
            'show_in_totem' => 'permit_empty',
            'internal_notes' => 'permit_empty|string',
            'collection_number' => 'permit_empty|string|max_length[255]',
            'collection_group' => 'permit_empty|string|max_length[255]',
            'physical_description' => 'permit_empty|string',
            'dimensions' => 'permit_empty|string|max_length[255]',
            'ingress_type' => 'permit_empty|string|max_length[255]',
            'donated_by' => 'permit_empty|string|max_length[255]',
            'tags' => 'permit_empty|string',
            'links' => 'permit_empty|string',
            'company_history' => 'permit_empty|string',
            'is_active' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        return [
            'name' => $this->postString('name'),
            'category_id' => $this->postInt('category_id'),
            'inventory_code' => $this->postString('inventory_code'),
            'status' => $this->postString('status'),
            'summary' => $this->postString('summary'),
            'curiosidad' => $this->postString('curiosidad'),
            'contenido' => $this->postString('contenido'),
            'origin' => $this->postString('origin'),
            'period' => $this->postString('period'),
            'creator' => $this->postString('creator'),
            'ubicacion' => $this->postString('ubicacion'),
            'materials' => $this->postString('materials'),
            'cover_file_id' => $this->postInt('cover_file_id'),
            'gallery_file_ids' => $this->postString('gallery_file_ids'),
            'show_in_totem' => $this->postBool('show_in_totem'),
            'internal_notes' => $this->postString('internal_notes'),
            'collection_number' => $this->postString('collection_number'),
            'collection_group' => $this->postString('collection_group'),
            'physical_description' => $this->postString('physical_description'),
            'dimensions' => $this->postString('dimensions'),
            'ingress_type' => $this->postString('ingress_type'),
            'donated_by' => $this->postString('donated_by'),
            'tags' => $this->postString('tags'),
            'links' => $this->postString('links'),
            'company_history' => $this->postString('company_history'),
            'is_active' => $this->postBool('is_active'),
        ];
    }
}
