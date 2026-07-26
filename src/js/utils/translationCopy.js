/* global HTMLTextAreaElement, HTMLSelectElement, Event */
/**
 * Shared "copy the base language value to every other language" primitive.
 *
 * Used both by langTabs() (multi-tab translatable resources: pages, entries,
 * categories, tags, collections, forms, menus) and directly as a global by
 * views with no tab UI (Settings, whose value widget swaps between
 * text/number/select/textarea depending on setting_type). Both contexts share
 * the same DOM shape: a source field and one or more target selectors that
 * may resolve to several sibling elements (only one of them enabled at a
 * time), so disabled matches are skipped rather than blindly overwritten.
 */

const isCopyableField = (el) => el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement || el instanceof HTMLSelectElement;

const readFieldValue = (el) => (el instanceof HTMLInputElement && el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value);

/**
 * Resolves a selector to the field that should be read/written.
 *
 * The common case (langTabs: pages, entries, categories, tags, collections,
 * forms, menus) is one element per selector, e.g. `translations[1][name]` —
 * that field is a valid target even while its language tab is hidden via
 * x-show, so a single match is always used as-is.
 *
 * Settings is the exception: its base-value selector matches one widget per
 * setting_type sharing the same resolved name, only one of them visible at a
 * time. There, and only there (more than one match), the hidden/disabled
 * candidates must be skipped so the copy doesn't leak into the wrong type's
 * field. Falls back to the first match if, unexpectedly, none are visible.
 * @param {string} selector
 * @returns {Element|null}
 */
const resolveActiveField = (selector) => {
    const matches = [...document.querySelectorAll(selector)].filter(isCopyableField);
    if (matches.length <= 1) {
        return matches[0] ?? null;
    }

    return matches.find((el) => !el.disabled && el.offsetParent !== null) ?? matches[0];
};

/**
 * Copies the value of a single source field into every target selector.
 * Both the source and target selectors may match more than one sibling
 * element; only the currently enabled match on each side is read/written.
 * @param {string} sourceSelector
 * @param {string[]} targetSelectors
 */
export function copyFieldToAll(sourceSelector, targetSelectors) {
    const sourceEl = resolveActiveField(sourceSelector);
    if (sourceEl === null) return;
    const value = readFieldValue(sourceEl);

    for (const targetSelector of targetSelectors) {
        const targetEl = resolveActiveField(targetSelector);
        if (targetEl !== null) {
            targetEl.value = value;
            targetEl.dispatchEvent(new Event('input', { bubbles: true }));
            targetEl.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}

/**
 * @param {Array<{source: string, targets: string[]}>} fieldMappings
 * @param {string} confirmationMessage
 * @returns {boolean} whether the copy was confirmed and applied
 */
export function copyDefaultToAll(fieldMappings, confirmationMessage = '') {
    if (!Array.isArray(fieldMappings) || fieldMappings.length === 0) return false;
    if (! window.confirm(confirmationMessage || 'Copy values to all languages? Existing values will be replaced.')) return false;

    fieldMappings.forEach(({ source, targets }) => {
        if (typeof source !== 'string' || !Array.isArray(targets)) return;
        copyFieldToAll(source, targets);
    });
    return true;
}
