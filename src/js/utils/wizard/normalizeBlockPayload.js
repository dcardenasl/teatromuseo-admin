export function normalizeBlockPayload(blockData) {
    const normalized = { ...(blockData ?? {}) };
    for (const [key, value] of Object.entries(normalized)) {
        if (!key.endsWith('_url')) continue;
        if (typeof value !== 'string' || value.trim() === '') continue;

        const fileIdKey = key.replace(/_url$/, '_file_id');
        if (normalized[fileIdKey] === undefined || normalized[fileIdKey] === null || normalized[fileIdKey] === '') {
            // Preserve image URLs even when the picker did not provide a file id.
            normalized[fileIdKey] = normalized[fileIdKey] ?? null;
        }
    }
    return normalized;
}
