export const isDev = typeof document !== 'undefined'
    && String(document.documentElement?.dataset?.env || '').toLowerCase() === 'development';

/** @param {...unknown} args */
export const devError = (...args) => { if (isDev) console.error(...args); };
