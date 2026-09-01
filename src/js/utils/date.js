import { localeTag } from './labels.js';

/**
 * @param {unknown} value
 * @returns {string|number|null}
 */
export const toDateInput = (value) => {
    if (value === null || value === undefined) return null;
    if (typeof value === 'string' || typeof value === 'number') return value;
    if (Array.isArray(value)) return value.length > 0 ? toDateInput(value[0]) : null;
    if (typeof value === 'object') {
        if (typeof value.date === 'string' || typeof value.date === 'number') return value.date;
        if (typeof value.datetime === 'string' || typeof value.datetime === 'number') return value.datetime;
        if (typeof value.created_at === 'string' || typeof value.created_at === 'number') return value.created_at;
        if (typeof value.value === 'string' || typeof value.value === 'number') return value.value;
    }
    return null;
};

const hasExplicitTimezone = (value) => /(?:Z|[+-]\d{2}:?\d{2})$/i.test(value);

const partsForTimezone = (date, timezone) => {
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone,
        calendar: 'gregory',
        numberingSystem: 'latn',
        hourCycle: 'h23',
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date);

    return Object.fromEntries(parts
        .filter(({ type }) => type !== 'literal')
        .map(({ type, value }) => [type, Number(value)]));
};

/**
 * Turn a wall-clock value into an instant using an IANA timezone.
 * This is needed for occurrence schedules, which intentionally have no
 * offset because they represent the venue's local clock.
 */
const parseInTimezone = (value, timezone) => {
    const match = /^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/.exec(value);
    if (!match) return new Date(value);

    const target = Date.UTC(
        Number(match[1]), Number(match[2]) - 1, Number(match[3]),
        Number(match[4]), Number(match[5]), Number(match[6] || 0)
    );
    let guess = new Date(target);

    // Recalculate once after applying the offset. A second pass covers a DST
    // boundary without adding a dependency just for timezone conversion.
    for (let index = 0; index < 2; index += 1) {
        const parts = partsForTimezone(guess, timezone);
        const represented = Date.UTC(
            parts.year, parts.month - 1, parts.day,
            parts.hour, parts.minute, parts.second
        );
        guess = new Date(target - (represented - guess.getTime()));
    }

    return guess;
};

/**
 * @param {unknown} value
 * @returns {string}
 */
export const formatDate = (value) => {
    const candidate = toDateInput(value);
    if (candidate === null || candidate === '') return '-';
    const raw = String(candidate);
    const normalized = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(?::\d{2})?$/.test(raw)
        ? raw.replace(' ', 'T')
        : raw;
    const objectValue = typeof value === 'object' && value !== null && !Array.isArray(value)
        ? value
        : null;
    const timezone = objectValue && typeof objectValue.timezone === 'string'
        ? objectValue.timezone
        : undefined;
    const configuredTimezone = typeof window.appTimezone === 'string' && window.appTimezone.trim() !== ''
        ? window.appTimezone
        : undefined;
    let date;
    try {
        date = timezone && !hasExplicitTimezone(normalized)
            ? parseInTimezone(normalized, timezone)
            : new Date(hasExplicitTimezone(normalized) ? normalized : `${normalized}Z`);
    } catch {
        return String(candidate);
    }
    if (Number.isNaN(date.getTime())) return String(candidate);
    return new Intl.DateTimeFormat(localeTag(), {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
        ...(timezone || configuredTimezone ? { timeZone: timezone || configuredTimezone } : {})
    }).format(date);
};
