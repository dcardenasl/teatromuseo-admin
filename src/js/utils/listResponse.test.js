import { describe, expect, it } from 'vitest';
import { extractListItems, extractListPagination, normalizeListRoot } from './listResponse.js';

describe('listResponse helpers', () => {
    it('normalizes nested list payloads and preserves row arrays', () => {
        const payload = {
            data: {
                data: [{ id: 1 }, { id: 2 }],
                meta: { total: 20, per_page: 10, page: 2, last_page: 2 },
            },
        };

        const root = normalizeListRoot(payload);
        expect(extractListItems(root)).toHaveLength(2);
    });

    it('derives last_page from total/per_page when the API omits it', () => {
        const root = {
            data: [{ id: 1 }, { id: 2 }, { id: 3 }],
            meta: { total: 73, per_page: 24, page: 2 },
        };

        const pagination = extractListPagination(root, {
            currentPage: 2,
            perPage: 24,
            visibleCount: 3,
        });

        expect(pagination.current_page).toBe(2);
        expect(pagination.last_page).toBe(4);
        expect(pagination.total_items).toBe(73);
        expect(pagination.from).toBe(25);
        expect(pagination.to).toBe(27);
    });

    it('keeps pagination stable when the API returns an underestimated last_page', () => {
        const root = {
            files: Array.from({ length: 24 }, (_, index) => ({ id: index + 1 })),
            meta: { total: 80, per_page: 24, page: 3, last_page: 2 },
        };

        const pagination = extractListPagination(root, {
            currentPage: 3,
            perPage: 24,
            visibleCount: 24,
        });

        expect(pagination.last_page).toBe(4);
        expect(pagination.current_page).toBe(3);
    });
});
