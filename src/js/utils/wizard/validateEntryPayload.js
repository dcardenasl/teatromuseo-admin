export function validateEntryPayload(payload, strings) {
    const errors = [];

    if (!payload || typeof payload !== 'object') {
        return [strings.error_publish];
    }

    if (!payload.collection_id || Number(payload.collection_id) <= 0) {
        errors.push(strings.error_collection_required);
    }

    if (String(payload.title || '').trim() === '') {
        errors.push(strings.error_title_required);
    }

    const translations = Array.isArray(payload.translations) ? payload.translations : [];
    translations.forEach((translation) => {
        const languageId = Number(translation?.language_id || 0);
        const prefix = languageId > 0 ? `translation[${languageId}]` : 'translation';
        const title = String(translation?.title || '').trim();
        const slug = String(translation?.slug || '').trim();

        if (title === '') {
            errors.push(`${prefix}.title required`);
        }
        if (title.length > 255) {
            errors.push(`${prefix}.title max_length 255`);
        }
        if (slug === '') {
            errors.push(`${prefix}.slug required`);
        }
        if (slug.length > 150) {
            errors.push(`${prefix}.slug max_length 150`);
        }
    });

    return errors;
}
