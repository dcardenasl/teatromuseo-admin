<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Requests;

use App\Support\Requests\BaseFormRequest;

class BookingStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['uuid', 'user_id', 'guest_email', 'total_amount', 'status', 'reserved_until', 'ticket_type_id', 'quantity', 'holder_name', 'holder_email'];
    }

    public function rules(): array
    {
        return [
            'uuid' => 'permit_empty|string|max_length[255]',
            'user_id' => 'permit_empty|integer',
            'guest_email' => 'permit_empty|string|max_length[255]',
            'total_amount' => 'required|decimal',
            'status' => 'required|in_list[pending,confirmed,cancelled,expired]',
            'reserved_until' => 'permit_empty|valid_date',
            'ticket_type_id' => 'required|is_natural_no_zero',
            'quantity' => 'required|is_natural_no_zero',
            'holder_name' => 'permit_empty|string|max_length[255]',
            'holder_email' => 'permit_empty|string|valid_email|max_length[255]',
        ];
    }

    public function payload(): array
    {
        return [
            'uuid' => $this->postString('uuid'),
            'user_id' => $this->postNullableInt('user_id'),
            'guest_email' => $this->postString('guest_email'),
            'total_amount' => (float) $this->postString('total_amount'),
            'status' => $this->postString('status'),
            'reserved_until' => $this->postString('reserved_until'),
            'ticket_type_id' => $this->postInt('ticket_type_id'),
            'quantity' => $this->postInt('quantity'),
            'holder_name' => $this->postString('holder_name'),
            'holder_email' => $this->postString('holder_email'),
        ];
    }
}
