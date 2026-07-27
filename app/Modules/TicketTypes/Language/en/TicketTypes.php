<?php

declare(strict_types=1);

return [
    'title'          => 'TicketType Management',
    'list'           => 'TicketType list',
    'create'         => 'Create tickettype',
    'edit'           => 'Edit tickettype',
    'show'           => 'TicketType details',
    'delete_confirm' => 'Are you sure you want to delete this tickettype?',
    'fields'         => [
        'event_id'             => 'Event ID',
        'event_id_placeholder' => 'Enter Event ID',
        'event_id_help'        => 'Parent event that owns this ticket type.',
        'name'             => 'Name',
        'name_placeholder' => 'General admission',
        'name_help'        => 'Public ticket type name.',
        'price'             => 'Price',
        'price_placeholder' => 'Enter Price',
        'price_help'        => 'Sale price for this ticket type.',
        'capacity'             => 'Capacity',
        'capacity_placeholder' => 'Enter Capacity',
        'capacity_help'        => 'Maximum number of tickets of this type.',
        'available_spots'             => 'Available Spots',
        'available_spots_placeholder' => 'Enter Available Spots',
        'available_spots_help'        => 'Remaining stock for this ticket type.',
        'sales_start'             => 'Sales Start',
        'sales_start_placeholder' => 'Enter Sales Start',
        'sales_start_help'        => 'Date and time when sales open.',
        'sales_end'             => 'Sales End',
        'sales_end_placeholder' => 'Enter Sales End',
        'sales_end_help'        => 'Date and time when sales close.',
    ],
];
