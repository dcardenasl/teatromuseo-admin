import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { csrfHeaders, adminFetch } from './adminFetch.js';

const CSRF = { name: 'csrf_test_name', token: 'csrf_test_token' };

describe('csrfHeaders', () => {
    it('builds the CSRF header pair using the dynamic field name', () => {
        expect(csrfHeaders(CSRF)).toEqual({
            'X-CSRF-TOKEN': 'csrf_test_token',
            csrf_test_name: 'csrf_test_token',
        });
    });
});

describe('adminFetch', () => {
    let originalFetch;

    beforeEach(() => {
        originalFetch = globalThis.fetch;
        globalThis.fetch = vi.fn().mockResolvedValue({ ok: true });
    });

    afterEach(() => {
        globalThis.fetch = originalFetch;
    });

    it('sends JSON content-type and CSRF headers for a plain body', async () => {
        await adminFetch('/wizard/publish', { method: 'POST', body: '{}' }, CSRF);

        expect(globalThis.fetch).toHaveBeenCalledWith('/wizard/publish', expect.objectContaining({
            credentials: 'same-origin',
            method: 'POST',
            body: '{}',
            headers: expect.objectContaining({
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': 'csrf_test_token',
                csrf_test_name: 'csrf_test_token',
            }),
        }));
    });

    it('omits Content-Type for FormData bodies', async () => {
        const fd = new FormData();
        await adminFetch('/wizard/upload', { method: 'POST', body: fd }, CSRF);

        const [, options] = globalThis.fetch.mock.calls[0];
        expect(options.headers['Content-Type']).toBeUndefined();
        expect(options.headers['X-CSRF-TOKEN']).toBe('csrf_test_token');
    });

    it('lets explicit opts.headers override the defaults', async () => {
        await adminFetch('/wizard/x', { headers: { 'X-Requested-With': 'custom' } }, CSRF);

        const [, options] = globalThis.fetch.mock.calls[0];
        expect(options.headers['X-Requested-With']).toBe('custom');
    });
});
