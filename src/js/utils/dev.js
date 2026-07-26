export const isDev = String(document.documentElement.dataset.env || '').toLowerCase() === 'development';

/** @param {...unknown} args */
export const devError = (...args) => { if (isDev) console.error(...args); };
