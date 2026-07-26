<?php

declare(strict_types=1);

return [
    // Index
    'title'    => 'Dynamic Forms',
    'subtitle' => 'Manage your site contact and enquiry forms.',
    'empty'    => 'No forms yet. Create your first form.',
    'loading'  => 'Loading forms',
    'search_placeholder' => 'Search by key or name...',

    // Columns
    'col_key'     => 'Key',
    'col_name'    => 'Name',
    'col_captcha' => 'CAPTCHA',
    'col_fields'  => 'Fields',
    'col_active'  => 'Active',
    'col_actions' => 'Actions',

    // Badges
    'captcha_on' => 'On',
    'captcha_off' => 'Off',

    // Page titles
    'create_title' => 'New Form',
    'edit_title'   => 'Edit Form',

    // Sections
    'section_general'      => 'General',
    'section_translations' => 'Translations',
    'section_fields'       => 'Field Builder',

    // Form fields — general
    'field_key'           => 'Form Key',
    'field_key_hint'      => 'Unique slug used in templates (e.g. contact). Alphanumeric, hyphens, underscores.',
    'field_key_readonly'  => 'Form key cannot be changed after creation.',
    'field_active'        => 'Active',
    'field_captcha'       => 'Require CAPTCHA',
    'field_captcha_hint'  => 'Enables validation for this form. reCAPTCHA keys are managed in CMS > Settings.',
    'field_notify_email'  => 'Notification Email',
    'field_notify_email_hint'       => 'Admin receives an email for every submission. Leave blank to disable.',
    'field_autoreply'               => 'Send autoreply to user',
    'field_autoreply_email_field'   => 'Email field key',
    'field_autoreply_email_field_hint' => 'The field_key of the email field (e.g. email). Used to send the autoreply.',

    // Form fields — translations
    'field_name'            => 'Form Name',
    'field_description'     => 'Description',
    'field_submit_label'    => 'Submit Button Label',
    'field_success_message' => 'Success Message',
    'field_error_message'   => 'Generic Error Message',

    // Field builder
    'fields_empty'       => 'No fields yet. Add your first field.',
    'btn_add_field'      => 'Add Field',
    'required'           => 'Required',

    // Field modal
    'modal_create_field' => 'Add Field',
    'modal_edit_field'   => 'Edit Field',

    // Field form
    'field_field_key'    => 'Field Key',
    'field_field_type'   => 'Type',
    'field_required'     => 'Required',
    'field_key_required' => 'The field key is required.',
    'field_label'        => 'Label',
    'field_placeholder'  => 'Placeholder',
    'field_help_text'    => 'Help Text',

    // Field types
    'field_type_text'     => 'Text',
    'field_type_email'    => 'Email',
    'field_type_phone'    => 'Phone',
    'field_type_textarea' => 'Textarea',
    'field_type_select'   => 'Dropdown (select)',
    'field_type_radio'    => 'Radio buttons (single choice)',
    'field_type_checkbox' => 'Checkboxes (multiple choice)',
    'field_type_date'     => 'Date',
    'field_type_number'   => 'Number',
    'field_type_url'      => 'URL',

    // Field options editor (select / radio / checkbox) — the option list
    // itself (add/remove/value) is structural and language-independent; each
    // option's display label is translatable and lives in the per-language
    // tab below, next to label/placeholder/help_text.
    'field_options'                  => 'Options',
    'field_options_structure_hint'   => 'Shown to visitors in the order listed. Add or remove options here — enter each one\'s translated label in the language tabs to the right.',
    'field_option_labels'            => 'Option labels',
    'field_option_labels_hint'       => 'The text visitors see for each option, in this language. The value stored on submission stays the same across all languages.',
    'option_value'                   => 'Value',
    'option_untitled'                => 'Missing label — fill it in on the language tabs →',
    'btn_add_option'                 => 'Add option',
    'btn_remove_option'              => 'Remove',
    'btn_regenerate_option_value'    => 'Regenerate value from label',
    'options_required'               => 'Add at least one option for this field type.',

    // Field-level auto-translate
    'btn_translate_field' => 'Auto-translate this field',

    // Buttons
    'btn_create' => 'New Form',
    'btn_save'   => 'Save',
    'btn_cancel' => 'Cancel',
    'btn_edit'   => 'Edit',
    'btn_delete' => 'Delete',

    // Confirmations
    'confirm_delete'       => 'Delete this form? This action cannot be undone.',
    'confirm_delete_field' => 'Delete this field? This cannot be undone.',

    // Flash messages
    'create_success' => 'Form created successfully.',
    'create_failed'  => 'Could not create the form.',
    'update_success' => 'Form updated successfully.',
    'update_failed'  => 'Could not update the form.',
    'delete_success' => 'Form deleted successfully.',
    'delete_failed'  => 'Could not delete the form.',
    'not_found'      => 'Form not found.',
    'in_use_warning' => 'This form is linked to {0} block(s). Remove those blocks before deleting the form.',

    // Field AJAX
    'save_field_failed' => 'Could not save the field.',

    // Show details page
    'show_title'        => 'Form Details',
    'translation_title' => 'Form Translations',
    'fields_title'      => 'Form Fields',
    'usages_title'      => 'Linked Usages',
    'usages_empty'      => 'This form is not linked to any page or entry block.',
    'usage_page'        => 'Page',
    'usage_entry'       => 'Entry',
    'usage_block'       => 'Block',
    'usage_instance'    => 'Instance',
    'usage_edit'        => 'Open block editor',
    'view_submissions'  => 'View Submissions',
];
