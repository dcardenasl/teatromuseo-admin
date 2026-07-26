import { isObject } from './url.js';

/** @param {object|null} file @returns {string} */
export const bestFileOriginalUrl = (file) => {
    return String(file?.url || '');
};

/** @param {object|null} file @returns {string} */
export const bestFilePreviewUrl = (file) => {
    const variants = isObject(file?.variants) ? file.variants : {};
    if (String(file?.mime_type || '').toLowerCase() === 'image/gif') {
        return String(file?.url || '');
    }
    return String(
        variants.md?.url || variants.sm?.url || variants.thumb?.url || file?.url || ''
    );
};

/**
 * @param {string|number|null|undefined} fileId
 * @param {string} fileUrl
 * @returns {string}
 */
export const resolveFilePreviewUrl = (fileId, fileUrl = '') => {
    const normalizedUrl = String(fileUrl || '').trim();
    if (normalizedUrl !== '') {
        return normalizedUrl;
    }

    const normalizedId = String(fileId || '').trim();
    if (normalizedId !== '') {
        return `/files/${encodeURIComponent(normalizedId)}/view`;
    }

    return '';
};

/**
 * @param {string} fileId
 * @param {string} fileUrl
 * @returns {string}
 */
export const resolveTranslatableFilePreviewUrl = (fileId, fileUrl = '') => {
    return resolveFilePreviewUrl(fileId, fileUrl);
};
