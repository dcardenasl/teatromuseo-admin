<?php

declare(strict_types=1);

return [
    'title'          => 'Gestión de Event',
    'list'           => 'Lista de events',
    'create'         => 'Crear event',
    'edit'           => 'Editar event',
    'show'           => 'Detalle de event',
    'delete_confirm' => '¿Estás seguro de que deseas eliminar este event?',
    'fields'         => [
        'uuid'             => 'UUID',
        'uuid_placeholder' => 'Ingresa UUID',
        'uuid_help'        => 'Stable identifier used by integrations.',
        'title'             => 'Título',
        'title_placeholder' => 'Annual meetup 2026',
        'title_help'        => 'Nombre público del evento visible para asistentes.',
        'event_type'             => 'Programming Type',
        'event_type_placeholder' => 'Ingresa Programming Type',
        'event_type_help'        => 'Classifies the activity as a function, festival, course, workshop, or other program.',
        'description'             => 'Description',
        'description_placeholder' => 'Explain what attendees can expect...',
        'description_help'        => 'Long-form event description for the detail page.',
        'status'             => 'Status',
        'status_placeholder' => 'Ingresa Status',
        'status_help'        => 'Controls whether the event is draft, published, or cancelled.',
    ],

    'sidebar_label' => 'Events',
];
