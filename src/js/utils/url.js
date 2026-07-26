/**
 * @param {string} search
 * @returns {Record<string, string>}
 */
export const queryToObject = (search) => {
    const params = new URLSearchParams(search);
    const query = {};
    params.forEach((value, key) => {
        const trimmed = value.trim();
        if (trimmed !== '') query[key] = trimmed;
    });
    return query;
};

/**
 * @param {Record<string, unknown>} query
 * @returns {string}
 */
export const objectToQueryString = (query) => {
    const params = new URLSearchParams();
    Object.entries(query || {}).forEach(([key, value]) => {
        if (typeof value === 'string' && value.trim() !== '') {
            params.append(key, value.trim());
        }
    });
    return params.toString();
};

/**
 * @param {HTMLFormElement} form
 * @returns {Record<string, string>}
 */
export const formToQuery = (form) => {
    const formData = new FormData(form);
    const query = {};
    formData.forEach((value, key) => {
        if (typeof value !== 'string') return;
        const trimmed = value.trim();
        if (trimmed !== '') query[key] = trimmed;
    });
    return query;
};

/**
 * @param {unknown} value
 * @returns {boolean}
 */
export const isObject = (value) => value !== null && typeof value === 'object' && !Array.isArray(value);

/**
 * Normalises an API list response to the object containing `{data, meta}`.
 * @param {unknown} payload
 * @returns {Record<string, unknown>}
 */
export const tablePayloadRoot = (payload) => {
    if (Array.isArray(payload)) return { data: payload };
    if (!isObject(payload)) return {};

    const nested = payload.data;
    if (!isObject(nested)) return payload;

    if (
        Array.isArray(nested.data) || isObject(nested.meta) ||
        nested.current_page !== undefined || nested.page !== undefined ||
        nested.last_page !== undefined || nested.total_items !== undefined ||
        isObject(nested.summary)
    ) {
        return nested;
    }

    return payload;
};
