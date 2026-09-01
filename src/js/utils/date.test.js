import { describe, expect, it } from 'vitest';

globalThis.window = { appTimezone: 'America/Santiago' };
globalThis.document = { documentElement: { lang: 'es' } };

const { formatDate } = await import('./date.js');

describe('date helpers', () => {
    it('converts a technical UTC timestamp to the configured venue timezone', () => {
        expect(formatDate('2026-08-19 01:24:00')).toContain('18/08/2026');
        expect(formatDate('2026-08-19 01:24:00')).toContain('21:24');
    });

    it('keeps venue wall-clock schedules in their declared timezone', () => {
        expect(formatDate({ date: '2026-08-19 10:00:00', timezone: 'America/Santiago' }))
            .toContain('19/08/2026');
        expect(formatDate({ date: '2026-08-19 10:00:00', timezone: 'America/Santiago' }))
            .toContain('10:00');
    });
});
