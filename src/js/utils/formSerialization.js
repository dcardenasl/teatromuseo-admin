function parseBracketPath(name) {
    const path = [];
    const source = String(name || '').trim();
    if (source === '') {
        return path;
    }

    const matcher = /([^[\]]+)|\[(.*?)\]/g;
    let match;
    while ((match = matcher.exec(source)) !== null) {
        const segment = match[1] ?? match[2] ?? '';
        if (segment !== '') {
            path.push(segment);
        }
    }

    return path;
}

function assignNestedValue(target, path, value) {
    if (!Array.isArray(path) || path.length === 0) {
        return target;
    }

    let cursor = target;
    for (let i = 0; i < path.length; i++) {
        const key = path[i];
        const isLast = i === path.length - 1;

        if (isLast) {
            cursor[key] = value;
            continue;
        }

        if (!Object.prototype.hasOwnProperty.call(cursor, key) || cursor[key] === null || typeof cursor[key] !== 'object' || Array.isArray(cursor[key])) {
            cursor[key] = {};
        }

        cursor = cursor[key];
    }

    return target;
}

export function formValuesToObject(form, allowedRoot = '') {
    if (!(form instanceof HTMLFormElement)) {
        return {};
    }

    const root = String(allowedRoot || '').trim();
    const payload = {};

    for (const [name, value] of new FormData(form).entries()) {
        const key = String(name || '');
        if (root !== '') {
            const prefix = `${root}[`;
            if (key !== root && !key.startsWith(prefix)) {
                continue;
            }
        }

        const path = parseBracketPath(key);
        if (root !== '' && path[0] === root) {
            path.shift();
        }

        if (path.length === 0) {
            continue;
        }

        assignNestedValue(payload, path, value);
    }

    return payload;
}
