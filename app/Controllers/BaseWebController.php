<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ApiClientInterface;
use App\Support\Requests\FormRequestInterface;
use App\Support\SessionKeys;
use App\Traits\TableResponseTrait;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Psr\Log\LoggerInterface;

abstract class BaseWebController extends BaseController
{
    use TableResponseTrait;

    protected ApiClientInterface $apiClient;

    protected \CodeIgniter\Session\Session $session;

    /** @var array<string, mixed> */
    protected array $viewData = [];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->apiClient = service('apiClient');
        $this->session = session();
        helper(['url', 'form']);

        /** @var \Config\ApiClient $apiConfig */
        $apiConfig = config('ApiClient');

        $this->viewData = [
            'appName'             => $apiConfig->appName,
            'user'                => $this->session->get(SessionKeys::USER->value),
            'currentLocale'       => Services::language()->getLocale(),
            'supportedLocales'    => config('App')->supportedLocales,
            // Absolute expiration timestamp (UTC seconds) for the current
            // access token. The layout publishes it as a <meta> tag so
            // client-side JS can show a warning before the session lapses,
            // instead of users getting a confusing 401 mid-action.
            'sessionExpiresAt'    => $this->session->get(SessionKeys::EXPIRES_AT->value),
        ];
    }

    /** @param array<string, mixed> $data */
    protected function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        return view($layout, array_merge($this->viewData, $data, [
            'view' => $view,
        ]));
    }

    /** @param array<string, mixed> $data */
    protected function renderAuth(string $view, array $data = []): string
    {
        return $this->render($view, $data, 'layouts/auth');
    }

    protected function withSuccess(string $message, string $redirectTo): RedirectResponse
    {
        return redirect()->to($redirectTo)->with('success', $message);
    }

    protected function withError(string $message, string $redirectTo): RedirectResponse
    {
        return redirect()->to($redirectTo)->with('error', $message);
    }

    /** @param array<string, mixed> $errors */
    protected function withFieldErrors(array $errors): RedirectResponse
    {
        return redirect()->back()->withInput()->with('fieldErrors', $errors);
    }

    protected function failValidation(): RedirectResponse
    {
        $errors = [];
        if (isset($this->validator) && $this->validator !== null) {
            $errors = $this->validator->getErrors();
        }

        return $this->withFieldErrors($errors);
    }

    protected function validateRequest(FormRequestInterface $request): ?RedirectResponse
    {
        if ($request->validate()) {
            return null;
        }

        if (ENVIRONMENT === 'development') {
            $errors = $request->errors();
            $this->flashDevError(
                422,
                json_encode([
                    'message'    => lang('App.errors_found'),
                    'fieldErrors' => $errors,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '',
                array_values($errors),
                $errors,
            );
        }

        return $this->withFieldErrors($request->errors());
    }

    /**
     * Build a consistent redirect response for failed API calls.
     *
     * @param array<string, mixed> $response
     * @param array<int, string> $allowedFieldErrors
     */
    protected function failApi(
        array $response,
        string $fallbackMessage,
        ?string $redirectTo = null,
        bool $withInput = true,
        array $allowedFieldErrors = [],
    ): RedirectResponse {
        $this->maybeFlashDevError($response);

        $fieldErrors = $this->getFieldErrors($response);

        if ($allowedFieldErrors !== []) {
            $fieldErrors = array_intersect_key($fieldErrors, array_flip($allowedFieldErrors));
        }

        if ($fieldErrors !== []) {
            return $this->withFieldErrors($fieldErrors);
        }

        $message = $this->firstMessage($response, $fallbackMessage);

        if ($redirectTo !== null && $redirectTo !== '') {
            return $this->withError($message, $redirectTo);
        }

        $redirect = redirect()->back();

        if ($withInput) {
            $redirect = $redirect->withInput();
        }

        return $redirect->with('error', $message);
    }

    /**
     * Store a development-only API/validation error snapshot for the flash
     * message panel. This keeps the exact upstream payload visible in the UI
     * so debugging stays fast without having to jump back to the terminal.
     *
     * @param list<string> $messages
     * @param array<string, string> $errors
     */
    protected function flashDevError(int $status, string $body, array $messages = [], array $errors = []): void
    {
        if (ENVIRONMENT !== 'development') {
            return;
        }

        session()->setFlashdata('devApiError', [
            'status'   => $status,
            'body'     => $body,
            'messages' => $messages,
            'errors'   => $errors,
        ]);
    }

    /**
     * Normalize a raw ApiClient/service response into the shape the dev error
     * panel expects.
     *
     * @param array<string, mixed> $response
     * @return array{status: int, body: string, messages: list<string>, errors: array<string, string>}
     */
    private function normalizeDevErr(array $response): array
    {
        $messages = is_array($response['messages'] ?? null) ? $response['messages'] : [];
        $errors   = is_array($response['fieldErrors'] ?? null) ? $response['fieldErrors'] : [];

        return [
            'status'   => (int) ($response['status'] ?? 0),
            'body'     => (string) ($response['raw'] ?? ''),
            'messages' => array_values(array_map(static fn (mixed $m): string => (string) $m, $messages)),
            'errors'   => array_map(static fn (mixed $e): string => (string) $e, $errors),
        ];
    }

    /**
     * Flash a dev error snapshot from a raw API response, if it failed. Use
     * this on any full-page render (GET or POST) that consumes safeApiCall()
     * results — the panel renders on the next response via flash_messages.php.
     *
     * @param array<string, mixed> $response
     */
    protected function maybeFlashDevError(array $response): void
    {
        if (ENVIRONMENT !== 'development' || ($response['ok'] ?? true) !== false) {
            return;
        }

        $err = $this->normalizeDevErr($response);
        log_message('debug', "[DEV] API call failed — HTTP {$err['status']}\n{$err['body']}");
        $this->flashDevError($err['status'], $err['body'], $err['messages'], $err['errors']);
    }

    /**
     * Render the dev error panel as inline HTML for a failed API response.
     * Use this instead of maybeFlashDevError() for AJAX/partial responses
     * (e.g. dashboard widgets) that get swapped into the DOM client-side —
     * flash session data never surfaces there since there's no follow-up
     * full-page render to display it on.
     *
     * @param array<string, mixed> $response
     */
    protected function renderDevApiErrorPanel(array $response): string
    {
        if (ENVIRONMENT !== 'development' || ($response['ok'] ?? true) !== false) {
            return '';
        }

        return view('layouts/partials/dev_api_error_panel', ['devErr' => $this->normalizeDevErr($response)]);
    }

    /**
     * Resolve the canonical public web URL used in API emails.
     */
    protected function clientBaseUrl(): string
    {
        $configured = trim((string) env('WEBAPP_BASE_URL', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        $appBaseUrl = trim((string) config('App')->baseURL);
        if ($appBaseUrl !== '') {
            return rtrim($appBaseUrl, '/');
        }

        return rtrim(site_url('/'), '/');
    }

    /**
     * Resolve the canonical base language from an active language list.
     *
     * The base language is the one that owns the canonical setting_value.
     * If no explicit default exists, fall back to the first language in the list.
     *
     * @param array<mixed> $languages
     */
    protected function resolveBaseLanguageId(array $languages): ?int
    {
        foreach ($languages as $language) {
            if (! is_array($language)) {
                continue;
            }

            if (! empty($language['is_default']) && isset($language['id']) && is_numeric($language['id'])) {
                return (int) $language['id'];
            }
        }

        foreach ($languages as $language) {
            if (! is_array($language)) {
                continue;
            }

            if (isset($language['id']) && is_numeric($language['id'])) {
                return (int) $language['id'];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, string>
     */
    protected function getFieldErrors(array $response): array
    {
        if (! isset($response['fieldErrors'])) {
            return [];
        }

        $fieldErrors = $response['fieldErrors'];

        if (! is_array($fieldErrors)) {
            log_message('warning', '[BaseWebController] Unexpected fieldErrors type: ' . gettype($fieldErrors));

            return [];
        }

        $normalized = [];

        foreach ($fieldErrors as $key => $value) {
            if (! is_string($key) || ! is_scalar($value)) {
                continue;
            }

            $normalized[$key] = $this->localizeApiMessage((string) $value);
        }

        return $normalized;
    }

    /**
     * Extract the first message from an API response array.
     *
     * @param array<string, mixed> $response
     */
    protected function firstMessage(array $response, string $fallback): string
    {
        foreach (['message', 'detail', 'title'] as $key) {
            if (isset($response[$key]) && is_scalar($response[$key])) {
                $message = $this->localizeApiMessage((string) $response[$key]);
                if ($message !== '') {
                    return $message;
                }
            }
        }

        $messages = $response['messages'] ?? [];

        if (is_array($messages) && isset($messages[0])) {
            return $this->localizeApiMessage((string) $messages[0]);
        }

        if (isset($response['errors']['general']) && is_scalar($response['errors']['general'])) {
            return $this->localizeApiMessage((string) $response['errors']['general']);
        }

        return $fallback;
    }

    /**
     * Map known API error codes to localized strings.
     *
     * The API (ci4-api-starter) returns snake_case error codes as message strings.
     * Translations live in app/Language/{locale}/ApiErrors.php so they stay in sync
     * with both supported locales. Add entries there when you discover new API codes.
     */
    protected function localizeApiMessage(string $message): string
    {
        $normalized = strtolower(trim($message));
        $localized  = lang('ApiErrors.' . $normalized);

        // lang() returns the key string (e.g. "ApiErrors.some_code") when not found.
        // Fall back to the original message to avoid showing raw key strings.
        if (is_string($localized) && ! str_starts_with($localized, 'ApiErrors.')) {
            return $localized;
        }

        return $message;
    }

    /**
     * Extract the nested 'data' items from an API list response.
     * Prioritizes the nested 'data' key commonly found in paginated responses.
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    protected function extractItems(array $response): array
    {
        if (isset($response['ok']) && ! $response['ok']) {
            return [];
        }

        $payload = $response['data'] ?? [];

        // In paginated responses: { data: { data: [...], meta: {...} } }
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        // In simple list responses: { data: [...] }
        return is_array($payload) ? $payload : [];
    }

    /**
     * Extract the nested 'data' payload from an API response.
     * Supports both single object and paginated list responses.
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    protected function extractData(array $response): array
    {
        if (isset($response['ok']) && ! $response['ok']) {
            return [];
        }

        $payload = $response['data'] ?? [];

        // Avoid returning the nested 'data' array if the payload is a pagination wrapper,
        // unless it's a simple wrapped object.
        if (isset($payload['data']) && is_array($payload['data']) && ! isset($payload['meta']) && ! isset($payload['current_page'])) {
            return $payload['data'];
        }

        return is_array($payload) ? $payload : [];
    }

    /**
     * Normalize an upstream API response's status code to a valid HTTP range,
     * falling back to 502 (Bad Gateway) when missing or out of bounds.
     *
     * @param array<string, mixed> $result
     */
    protected function normalizeUpstreamStatus(array $result): int
    {
        $status = (int) ($result['status'] ?? 502);

        return ($status < 100 || $status > 599) ? 502 : $status;
    }

    /**
     * Extract the raw JSON body of the current request as an array.
     *
     * @return array<string, mixed>
     */
    protected function jsonRequestPayload(): array
    {
        $raw = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest
            ? ($this->request->getJSON(true) ?? [])
            : [];

        return is_array($raw) ? $raw : [];
    }

    /**
     * Wrap an API call in a try/catch, returning a graceful error response on failure.
     *
     * @param callable $callback A closure that performs the API call and returns its result.
     * @return array<string, mixed> The API response array, or a synthetic error response on exception.
     */
    protected function safeApiCall(callable $callback): array
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            log_message('error', 'API call failed: ' . $e->getMessage());

            return [
                'ok'          => false,
                'status'      => 0,
                'data'        => [],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [lang('App.connection_error')],
                'fieldErrors' => [],
            ];
        }
    }

    protected function positiveIntFromQuery(string $key, int $default, int $max = 200): int
    {
        $raw = $this->request->getGet($key);
        $value = is_numeric($raw) ? (int) $raw : $default;

        if ($value <= 0) {
            $value = $default;
        }

        return min($value, $max);
    }

    /**
     * Render a resource detail view with a consistent not-found fallback.
     *
     * @param array<string, mixed> $response
     */
    protected function renderResourceShow(
        string $view,
        string $title,
        string $dataKey,
        array $response,
        string $notFoundMessage,
    ): string {
        $data = [
            'title' => $title,
            $dataKey => [],
        ];

        if (! ($response['ok'] ?? false)) {
            $this->maybeFlashDevError($response);
            $data['error'] = $this->firstMessage($response, $notFoundMessage);

            return $this->render($view, $data);
        }

        $data[$dataKey] = $this->extractData($response);

        return $this->render($view, $data);
    }

    /**
     * Resolve the active language context once from the global language preset.
     *
     * @param array<array-key, mixed> $languages
     * @return array{defaultLangId: int, defaultLangCode: string, defaultLangIndex: int}
     */
    protected function resolveLanguageContext(array $languages): array
    {
        $defaultLangIndex = 0;
        $defaultLangCode = '';
        $defaultLangId = 0;

        if (! empty($languages)) {
            foreach ($languages as $index => $language) {
                if (! is_array($language)) {
                    continue;
                }

                if (! empty($language['is_default']) && isset($language['id']) && is_numeric($language['id'])) {
                    return [
                        'defaultLangId' => (int) $language['id'],
                        'defaultLangCode' => (string) ($language['code'] ?? ''),
                        'defaultLangIndex' => (int) $index,
                    ];
                }
            }

            $defaultLangId = (int) service('languageApiService')->defaultId();

            if ($defaultLangId > 0) {
                foreach ($languages as $index => $language) {
                    if (! is_array($language)) {
                        continue;
                    }

                    if ((int) ($language['id'] ?? 0) === $defaultLangId) {
                        return [
                            'defaultLangId' => $defaultLangId,
                            'defaultLangCode' => (string) ($language['code'] ?? ''),
                            'defaultLangIndex' => (int) $index,
                        ];
                    }
                }
            }

            foreach ($languages as $index => $language) {
                if (! is_array($language) || ! isset($language['id']) || ! is_numeric($language['id'])) {
                    continue;
                }

                $defaultLangId = (int) $language['id'];
                $defaultLangIndex = (int) $index;
                $defaultLangCode = (string) ($language['code'] ?? '');
                break;
            }
        }

        return [
            'defaultLangId' => $defaultLangId,
            'defaultLangCode' => $defaultLangCode,
            'defaultLangIndex' => $defaultLangIndex,
        ];
    }

    /**
     * Reads `return_to` from the current GET query so an edit view can echo it
     * back into a hidden form field. Validated here too so the round trip
     * never carries something unsafe, even though the real security gate is
     * {@see resolveReturnUrl()} at submit time.
     */
    protected function incomingReturnTo(): string
    {
        $value = $this->request->getGet('return_to');

        return is_string($value) && $this->isSafeLocalReturnUrl($value) ? $value : '';
    }

    /**
     * Resolves the redirect target for a save action: honors a posted
     * `return_to` (e.g. "back to the translation audit workbench, filters
     * preserved") when it is present and safe, otherwise falls back to the
     * resource's own default destination.
     */
    protected function resolveReturnUrl(string $fallbackUrl): string
    {
        $returnTo = $this->request->getPost('return_to');

        return is_string($returnTo) && $this->isSafeLocalReturnUrl($returnTo) ? $returnTo : $fallbackUrl;
    }

    /**
     * Guards against open-redirect: only a same-app, absolute path is
     * accepted — never a scheme, a protocol-relative `//host` URL, or a
     * backslash trick some browsers normalize into one.
     */
    private function isSafeLocalReturnUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || $url[0] !== '/' || str_starts_with($url, '//')) {
            return false;
        }

        return ! str_contains($url, '\\') && ! preg_match('/[\r\n]/', $url);
    }

    /**
     * Build translation targets for automatic translation functionality.
     * Centralizado método que genera la configuración de traducción automática.
     *
     * @param array $languages Array de idiomas disponibles (cada uno debe tener 'id', 'code', 'is_default')
     * @param array $fieldMap Mapa de campos a traducir: ['fieldName' => 'fieldKey', ...]
     *                        Donde fieldKey es usado para construir el selector CSS
     * @param int $defaultLangId ID del idioma por defecto
     * @param string $prefix Prefijo para los selectores CSS (default: 'translations')
     * @param bool $keyByLanguageId Si es true, los selectores usan el `id` real del idioma como
     *                              índice del array (`translations[{id}][campo]`) en vez de la
     *                              posición dentro de $languages (`translations[{posicion}][campo]`).
     *                              Usar true solo para formularios cuyos inputs están nombrados
     *                              por language id (p. ej. menu items); el resto de módulos de
     *                              contenido nombra sus inputs por posición y debe dejar el default.
     * @return array Array de targets compatible con Alpine.js autoTranslateAll()
     *
     * @example
     *   $targets = $this->buildTranslateTargets(
     *       $languages,
     *       ['title' => 'title', 'excerpt' => 'excerpt'],
     *       $defaultLangId
     *   );
     */
    /**
     * @param array<array-key, mixed>  $languages
     * @param array<array-key, string> $fieldMap
     * @return list<array{langCode: string, fieldPairs: list<array{from: string, to: string}>}>
     */
    protected function buildTranslateTargets(
        array $languages,
        array $fieldMap,
        int $defaultLangId,
        string $prefix = 'translations',
        bool $keyByLanguageId = false,
    ): array {
        if (empty($fieldMap) || empty($languages)) {
            return [];
        }

        $targets = [];
        $defaultLangIndex = 0;

        if ($keyByLanguageId) {
            $defaultLangIndex = $defaultLangId;
        } else {
            // Encontrar el índice del idioma por defecto
            foreach ($languages as $idx => $lang) {
                if ((int) ($lang['id'] ?? 0) === $defaultLangId) {
                    $defaultLangIndex = $idx;
                    break;
                }
            }
        }

        // Para cada idioma no-default, crear los field pairs
        foreach ($languages as $idx => $lang) {
            $langKey = $keyByLanguageId ? (int) ($lang['id'] ?? 0) : $idx;
            if ($langKey === $defaultLangIndex) {
                continue;
            }

            $langCode = strtoupper((string) ($lang['code'] ?? 'EN'));
            $fieldPairs = [];

            // Construir los selectores para cada campo
            foreach ($fieldMap as $fieldName) {
                $fieldPairs[] = [
                    'from' => sprintf('[name="%s[%d][%s]"]', $prefix, $defaultLangIndex, $fieldName),
                    'to'   => sprintf('[name="%s[%d][%s]"]', $prefix, $langKey, $fieldName),
                ];
            }

            $targets[] = [
                'langCode'   => $langCode,
                'fieldPairs' => $fieldPairs,
            ];
        }

        return $targets;
    }
}
