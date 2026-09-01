<?php

declare(strict_types=1);

namespace App\Modules\TicketTypes\Requests;

use App\Support\Requests\BaseFormRequest;

class TicketTypeStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['event_id', 'occurrence_id', 'name', 'price', 'capacity', 'available_spots', 'sales_start', 'sales_end'];
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required',
            'occurrence_id' => 'permit_empty',
            'name' => 'required|min_length[2]|max_length[255]',
            'price' => 'required|decimal',
            'capacity' => 'required|integer',
            'available_spots' => 'required|integer',
            'sales_start' => 'required|valid_date',
            'sales_end' => 'required|valid_date',
        ];
    }

    public function payload(): array
    {
        return [
            'event_id' => $this->postInt('event_id'),
            'occurrence_id' => $this->postInt('occurrence_id'),
            'name' => $this->postString('name'),
            'price' => (float) $this->postString('price'),
            'capacity' => $this->postInt('capacity'),
            'available_spots' => $this->postInt('available_spots'),
            'sales_start' => $this->postString('sales_start'),
            'sales_end' => $this->postString('sales_end'),
        ];
    }
}
