<?php

declare(strict_types=1);

return [
    'title'          => 'Event Management',
    'list'           => 'Event list',
    'create'         => 'Create event',
    'edit'           => 'Edit event',
    'show'           => 'Event details',
    'delete_confirm' => 'Are you sure you want to delete this event?',
    'fields'         => [
        'uuid'             => 'UUID',
        'uuid_placeholder' => 'Enter UUID',
        'uuid_help'        => 'Stable identifier used by integrations.',
        'title'             => 'Title',
        'title_placeholder' => 'Annual meetup 2026',
        'title_help'        => 'Public event name shown to attendees.',
        'event_type'             => 'Programming Type',
        'event_type_placeholder' => 'Enter Programming Type',
        'event_type_help'        => 'Classifies the activity as a function, festival, course, workshop, or other program.',
        'description'             => 'Description',
        'description_placeholder' => 'Explain what attendees can expect...',
        'description_help'        => 'Long-form event description for the detail page.',
        'status'             => 'Status',
        'status_placeholder' => 'Enter Status',
        'status_help'        => 'Controls whether the event is draft, published, or cancelled.',
    ],

    'sidebar_label' => 'Events',
];
