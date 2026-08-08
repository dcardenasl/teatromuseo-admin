/**
 * Concurrency-limited fetch queue. Callers get the same Promise-based
 * `fetch()` contract, but only `maxConcurrent` requests are in flight at
 * once — the rest wait in FIFO order until a slot frees up.
 *
 * @param {number} maxConcurrent
 * @returns {{ fetch: (url: string, options?: RequestInit) => Promise<Response> }}
 */
export const createFetchQueue = (maxConcurrent = 3) => {
    let active = 0;
    const pending = [];

    const runNext = () => {
        if (active >= maxConcurrent || pending.length === 0) return;
        const { url, options, resolve, reject } = pending.shift();
        active += 1;
        fetch(url, options)
            .then(resolve, reject)
            .finally(() => {
                active -= 1;
                runNext();
            });
    };

    return {
        fetch(url, options) {
            return new Promise((resolve, reject) => {
                pending.push({ url, options, resolve, reject });
                runNext();
            });
        },
    };
};
