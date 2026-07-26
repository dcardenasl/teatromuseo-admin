<?php

declare(strict_types=1);

return [
    'settings_title'           => 'Settings',
    'settings_new'             => 'New Setting',
    'settings_create'          => 'New Setting',
    'settings_edit'            => 'Edit Setting',
    'settings_details'         => 'Setting details',
    'settings_not_found'       => 'Setting not found.',
    'settings_create_success'  => 'Setting created successfully.',
    'settings_create_failed'   => 'Could not create the setting.',
    'settings_update_success'  => 'Setting updated successfully.',
    'settings_update_failed'   => 'Could not update the setting.',
    'settings_delete_success'  => 'Setting deleted successfully.',
    'settings_delete_failed'   => 'Could not delete the setting.',
    'settings_loading'         => 'Loading settings...',
    'settings_search_placeholder' => 'Search settings...',
    'settings_translations'    => 'Translations',
    'settings_value_section'   => 'Value settings',
    'settings_metadata_section' => 'Setting details',
    'field_base_value'         => 'Base value',
    'field_base_value_help'    => 'This is the canonical value the system treats as the source of truth.',

    'field_setting_key'           => 'Setting key',
    'field_setting_key_placeholder' => 'site.name',
    'field_setting_key_help'      => 'Unique key used to read this setting.',
    'field_setting_value'         => 'Setting value',
    'field_setting_value_placeholder' => 'Enter setting value',
    'field_setting_type'          => 'Setting type',
    'field_setting_type_placeholder' => 'Select setting type',
    'field_setting_type_help'     => 'Controls how the setting value is stored.',
    'field_setting_group'         => 'Group',
    'field_setting_group_placeholder' => 'general',
    'field_setting_group_help'    => 'Group used to organize settings.',
    'group_identity'              => 'Identity',
    'group_contact'               => 'Contact',
    'group_integration'           => 'Integrations',
    'group_analytics'             => 'Analytics',
    'group_social'                => 'Social media',
    'field_description'           => 'Description',
    'field_description_placeholder' => 'Optional description',
    'field_description_help'      => 'Short internal description.',
    'field_is_translatable'       => 'Translatable',
    'field_is_translatable_help'  => 'Enable per-language translations for this setting.',
    'field_is_translatable_on'    => 'Translatable',
    'field_is_translatable_off'   => 'Not translatable',
    'field_language'              => 'Language',

    // UI control type
    'field_input_type'            => 'UI control type',
    'field_input_type_help'       => 'Determines which form component renders this setting in the admin.',
    'input_type_text'             => 'Text',
    'input_type_textarea'         => 'Textarea',
    'input_type_richtext'         => 'Rich text',
    'input_type_url'              => 'URL',
    'input_type_email'            => 'Email',
    'input_type_phone'            => 'Phone',
    'input_type_color'            => 'Color picker',
    'input_type_number'           => 'Number',
    'input_type_boolean'          => 'Toggle (on/off)',
    'input_type_image'            => 'Image picker',
    'input_type_file'             => 'File picker',
    'input_type_select'           => 'Dropdown select',
    'input_type_code'             => 'Code / JSON',
    'input_type_slug'             => 'Slug',

    // Select options JSON
    'field_options_json'          => 'Select options (JSON)',
    'field_options_json_placeholder' => '[{"value":"opt1","label":"Option 1"},{"value":"opt2","label":"Option 2"}]',
    'field_options_json_help'     => 'Array of {value, label} objects. Only used when UI control type is "Dropdown select".',

    // Required / readonly
    'field_is_required'           => 'Required',
    'field_is_readonly'           => 'Read-only',

    // UI labels per language (inside translations section)
    'ui_labels_section'           => 'UI labels (label / placeholder / help text)',
    'field_label_placeholder'     => 'Field label',
    'field_placeholder_placeholder' => 'Placeholder text',
    'field_help_text_placeholder' => 'Help text shown below the field',

    // Key used in settings.key_must_be_unique language string
    'key_must_be_unique'          => 'This setting key is already in use.',
];
