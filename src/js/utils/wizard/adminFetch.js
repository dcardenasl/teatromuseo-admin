export function csrfHeaders(csrf) {
    return { 'X-CSRF-TOKEN': csrf.token, [csrf.name]: csrf.token };
}

export async function adminFetch(url, opts = {}, csrf) {
    const isFormData = opts.body instanceof FormData;
    const headers = isFormData
        ? csrfHeaders(csrf)
        : { 'Content-Type': 'application/json', ...csrfHeaders(csrf) };
    return fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', ...headers, ...(opts.headers || {}) },
        ...opts,
    });
}
