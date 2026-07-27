<?php

declare(strict_types=1);

return [
    'title'          => 'Gestión de EventReference',
    'list'           => 'Lista de eventreferences',
    'create'         => 'Crear eventreference',
    'edit'           => 'Editar eventreference',
    'show'           => 'Detalle de eventreference',
    'delete_confirm' => '¿Estás seguro de que deseas eliminar este eventreference?',
    'fields'         => [
        'event_id'             => 'Event ID',
        'event_id_placeholder' => 'Ingresa Event ID',
        'event_id_help'        => 'Event aggregate that owns the reference.',
        'source_system'             => 'Source System',
        'source_system_placeholder' => 'Ingresa Source System',
        'source_system_help'        => 'Origin system, such as catalog, cms, or legacy.',
        'source_type'             => 'Source Type',
        'source_type_placeholder' => 'Ingresa Source Type',
        'source_type_help'        => 'Origin entity type.',
        'source_id'             => 'Source ID',
        'source_id_placeholder' => 'Ingresa Source ID',
        'source_id_help'        => 'Identifier in the origin system.',
        'relation'             => 'Relation',
        'relation_placeholder' => 'Ingresa Relation',
        'relation_help'        => 'Meaning of the reference, such as work or editorial page.',
        'metadata'             => 'Metadata',
        'metadata_placeholder' => 'Ingresa Metadata',
        'metadata_help'        => 'Optional integration metadata.',
    ],
];
