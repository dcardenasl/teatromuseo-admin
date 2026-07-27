<?php

declare(strict_types=1);

return [
    'title'          => 'Gestión de TicketType',
    'list'           => 'Lista de tickettypes',
    'create'         => 'Crear tickettype',
    'edit'           => 'Editar tickettype',
    'show'           => 'Detalle de tickettype',
    'delete_confirm' => '¿Estás seguro de que deseas eliminar este tickettype?',
    'fields'         => [
        'event_id'             => 'Event ID',
        'event_id_placeholder' => 'Ingresa Event ID',
        'event_id_help'        => 'Parent event that owns this ticket type.',
        'name'             => 'Name',
        'name_placeholder' => 'General admission',
        'name_help'        => 'Public ticket type name.',
        'price'             => 'Price',
        'price_placeholder' => 'Ingresa Price',
        'price_help'        => 'Sale price for this ticket type.',
        'capacity'             => 'Capacity',
        'capacity_placeholder' => 'Ingresa Capacity',
        'capacity_help'        => 'Maximum number of tickets of this type.',
        'available_spots'             => 'Available Spots',
        'available_spots_placeholder' => 'Ingresa Available Spots',
        'available_spots_help'        => 'Remaining stock for this ticket type.',
        'sales_start'             => 'Sales Start',
        'sales_start_placeholder' => 'Ingresa Sales Start',
        'sales_start_help'        => 'Date and time when sales open.',
        'sales_end'             => 'Sales End',
        'sales_end_placeholder' => 'Ingresa Sales End',
        'sales_end_help'        => 'Date and time when sales close.',
    ],
];
