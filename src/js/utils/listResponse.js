import { isObject, tablePayloadRoot } from './url.js';

const DEFAULT_LIST_KEYS = ['data', 'items', 'files', 'results'];

const toPositiveInt = (value, fallback) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) && parsed > 0 ? Math.floor(parsed) : fallback;
};

/**
 * Normalise a list payload to the object that actually carries the rows/meta.
 *
 * @param {unknown} payload
 * @returns {Record<string, unknown>}
 */
export const normalizeListRoot = (payload) => tablePayloadRoot(payload);

/**
 * Extract rows from a paginated list payload.
 *
 * @param {Record<string, unknown>} root
 * @returns {Array<unknown>}
 */
export const extractListItems = (root) => {
    if (!isObject(root)) return [];

    if (Array.isArray(root.data)) return root.data;
    if (isObject(root.data) && Array.isArray(root.data.data)) return root.data.data;
    if (Array.isArray(root.items)) return root.items;

    for (const key of DEFAULT_LIST_KEYS) {
        const value = root[key];
        if (Array.isArray(value)) return value;
        if (isObject(value) && Array.isArray(value.data)) return value.data;
    }

    return [];
};

/**
 * Extract the canonical summary block from a list payload, if present.
 *
 * @param {Record<string, unknown>} root
 * @returns {Record<string, unknown>}
 */
export const extractListSummary = (root) => {
    if (isObject(root.summary)) return root.summary;
    if (isObject(root.data) && isObject(root.data.summary)) return root.data.summary;
    return {};
};

/**
 * Build resilient pagination metadata from a list payload.
 *
 * @param {Record<string, unknown>} root
 * @param {{ currentPage?: number, perPage?: number, visibleCount?: number, cursor?: string }} [options]
 * @returns {{ mode: 'page' | 'cursor', current_page: number, last_page: number, total_items: number, limit: number, from: number, to: number, next_cursor: string, prev_cursor: string }}
 */
export const extractListPagination = (root, options = {}) => {
    const meta = isObject(root.meta) ? root.meta : {};
    const currentPageFallback = toPositiveInt(options.currentPage, 1);
    const perPageFallback = toPositiveInt(options.perPage, 25);
    const visibleCount = toPositiveInt(options.visibleCount, 0);
    const queryCursor = String(options.cursor || '').trim();

    const next_cursor = String(meta.next_cursor ?? root.next_cursor ?? '').trim();
    const prev_cursor = String(meta.prev_cursor ?? root.prev_cursor ?? '').trim();
    const hasCursor = next_cursor !== '' || prev_cursor !== '' || queryCursor !== '';

    const limit = toPositiveInt(
        meta.per_page ?? meta.limit ?? root.per_page ?? root.limit ?? perPageFallback,
        perPageFallback
    );
    const total = Math.max(
        0,
        toPositiveInt(
            meta.total_items ?? meta.total ?? root.total_items ?? root.total ?? visibleCount,
            visibleCount
        )
    );
    const current_page = toPositiveInt(
        meta.current_page ?? meta.page ?? root.current_page ?? root.page ?? currentPageFallback,
        currentPageFallback
    );
    const explicitLastPage = toPositiveInt(meta.last_page ?? root.last_page, 0);
    const derivedLastPage = total > 0 ? Math.max(1, Math.ceil(total / limit)) : 1;
    const last_page = Math.max(derivedLastPage, explicitLastPage || 1);
    const normalizedCurrentPage = Math.max(1, Math.min(current_page, last_page));
    const from = total <= 0 ? 0 : ((normalizedCurrentPage - 1) * limit) + 1;
    const to = total <= 0
        ? 0
        : (visibleCount > 0
            ? Math.min(total, from + visibleCount - 1)
            : Math.min(total, normalizedCurrentPage * limit));

    return {
        mode: hasCursor ? 'cursor' : 'page',
        current_page: normalizedCurrentPage,
        last_page: Math.max(1, last_page),
        total_items: total,
        limit,
        from: Math.max(0, from),
        to: Math.max(0, to),
        next_cursor,
        prev_cursor,
    };
};
