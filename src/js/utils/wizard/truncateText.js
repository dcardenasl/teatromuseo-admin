export function truncateText(value, maxLength) {
    const text = String(value ?? '').trim();
    if (text === '' || text.length <= maxLength) {
        return text;
    }

    return text.slice(0, maxLength).trim();
}
