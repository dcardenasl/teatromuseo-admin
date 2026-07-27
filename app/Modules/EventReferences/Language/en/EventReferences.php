<?php

declare(strict_types=1);

return [
    'title'          => 'EventReference Management',
    'list'           => 'EventReference list',
    'create'         => 'Create eventreference',
    'edit'           => 'Edit eventreference',
    'show'           => 'EventReference details',
    'delete_confirm' => 'Are you sure you want to delete this eventreference?',
    'fields'         => [
        'event_id'             => 'Event ID',
        'event_id_placeholder' => 'Enter Event ID',
        'event_id_help'        => 'Event aggregate that owns the reference.',
        'source_system'             => 'Source System',
        'source_system_placeholder' => 'Enter Source System',
        'source_system_help'        => 'Origin system, such as catalog, cms, or legacy.',
        'source_type'             => 'Source Type',
        'source_type_placeholder' => 'Enter Source Type',
        'source_type_help'        => 'Origin entity type.',
        'source_id'             => 'Source ID',
        'source_id_placeholder' => 'Enter Source ID',
        'source_id_help'        => 'Identifier in the origin system.',
        'relation'             => 'Relation',
        'relation_placeholder' => 'Enter Relation',
        'relation_help'        => 'Meaning of the reference, such as work or editorial page.',
        'metadata'             => 'Metadata',
        'metadata_placeholder' => 'Enter Metadata',
        'metadata_help'        => 'Optional integration metadata.',
    ],
];
