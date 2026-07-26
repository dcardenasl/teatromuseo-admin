export function defaultSteps(strings) {
    return [
        {
            step_title: strings.default_step1_title,
            step_hint:  strings.default_step1_hint,
            fields: [{ key: 'title', label: strings.default_field_title, type: 'text', required: true }],
        },
        {
            step_title: strings.default_step2_title,
            step_hint:  strings.default_step2_hint,
            fields: [{ key: 'featured_image', label: strings.default_field_image, type: 'image', required: false }],
        },
        {
            step_title: strings.default_step3_title,
            step_hint:  strings.default_step3_hint,
            fields: [{ key: 'excerpt', label: strings.default_field_excerpt, type: 'textarea', required: false }],
        },
    ];
}
