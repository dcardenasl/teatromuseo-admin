<?php

declare(strict_types=1);

return [
    'title'          => 'Booking Management',
    'list'           => 'Booking list',
    'create'         => 'Create booking',
    'edit'           => 'Edit booking',
    'show'           => 'Booking details',
    'delete_confirm' => 'Are you sure you want to delete this booking?',
    'fields'         => [
        'uuid'             => 'UUID',
        'uuid_placeholder' => 'Enter UUID',
        'uuid_help'        => 'Stable identifier used by integrations.',
        'user_id'             => 'User ID',
        'user_id_placeholder' => 'Enter User ID',
        'user_id_help'        => 'Registered user who made the booking.',
        'guest_email'             => 'Guest Email',
        'guest_email_placeholder' => 'guest@example.com',
        'guest_email_help'        => 'Email for guests that check out without an account.',
        'total_amount'             => 'Total Amount',
        'total_amount_placeholder' => 'Enter Total Amount',
        'total_amount_help'        => 'Final amount charged for the booking.',
        'status'             => 'Status',
        'status_placeholder' => 'Enter Status',
        'status_help'        => 'Current booking lifecycle state.',
        'reserved_until'             => 'Reserved Until',
        'reserved_until_placeholder' => 'Enter Reserved Until',
        'reserved_until_help'        => 'Expiration timestamp for the booking reservation.',
    ],
];
