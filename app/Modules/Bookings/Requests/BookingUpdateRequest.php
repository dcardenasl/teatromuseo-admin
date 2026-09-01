<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Requests;

use App\Support\Requests\BaseFormRequest;

class BookingUpdateRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['user_id', 'guest_email', 'total_amount', 'status', 'reserved_until'];
    }

    public function rules(): array
    {
        return [
            'user_id' => 'permit_empty|integer',
            'guest_email' => 'permit_empty|string|max_length[255]',
            'total_amount' => 'required|decimal',
            'status' => 'required|in_list[pending,confirmed,cancelled,expired]',
            'reserved_until' => 'permit_empty|valid_date',
        ];
    }

    public function payload(): array
    {
        return [
            'user_id' => $this->postNullableInt('user_id'),
            'guest_email' => $this->postString('guest_email'),
            'total_amount' => (float) $this->postString('total_amount'),
            'status' => $this->postString('status'),
            'reserved_until' => $this->postString('reserved_until'),
        ];
    }
}
