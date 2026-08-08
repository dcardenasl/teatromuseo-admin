import { afterEach, describe, expect, it, vi } from 'vitest';
import { createFetchQueue } from './fetchQueue.js';

const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

describe('createFetchQueue', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('never runs more than maxConcurrent requests at once', async () => {
        let inFlight = 0;
        let peak = 0;

        vi.stubGlobal('fetch', async () => {
            inFlight += 1;
            peak = Math.max(peak, inFlight);
            await delay(20);
            inFlight -= 1;
            return { status: 200 };
        });

        const queue = createFetchQueue(2);
        await Promise.all([
            queue.fetch('/a'),
            queue.fetch('/b'),
            queue.fetch('/c'),
            queue.fetch('/d'),
        ]);

        expect(peak).toBe(2);
    });

    it('runs every queued request exactly once', async () => {
        let calls = 0;
        vi.stubGlobal('fetch', async () => {
            calls += 1;
            return { status: 200 };
        });

        const queue = createFetchQueue(1);
        await Promise.all([queue.fetch('/a'), queue.fetch('/b'), queue.fetch('/c')]);

        expect(calls).toBe(3);
    });
});
