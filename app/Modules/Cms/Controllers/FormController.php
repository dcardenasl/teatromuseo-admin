<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class FormController extends BaseWebController
{
    protected FormApiService $formService;
    protected LanguageApiService $languageService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->formService     = service('formApiService');
        $this->languageService = service('languageApiService');
    }

    public function index(): string
    {
        $langResponse = $this->safeApiCall(fn () => $this->languageService->list());
        if (! $langResponse['ok']) {
            $this->maybeFlashDevError($langResponse);
        }
        $languages = $this->extractItems($langResponse) ?: [];

        return $this->render('cms/forms/index', [
            'title'        => lang('Forms.title'),
            'limitOptions' => [10, 25, 50, 100],
            'languages'    => $languages,
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['form_key', 'is_active', 'created_at'],
            fn (array $params) => $this->formService->list([...$params, 'include_translations' => 1]),
        );
    }

    public function show(string $id): string
    {
        $formResponse = $this->safeApiCall(fn () => $this->formService->get($id));

        if (! $formResponse['ok']) {
            $this->maybeFlashDevError($formResponse);

            return $this->render('cms/forms/show', [
                'title'     => lang('Forms.show_title'),
                'form'      => [],
                'languages' => [],
                'error'     => $this->firstMessage($formResponse, lang('Forms.not_found')),
            ]);
        }

        $langResponse = $this->safeApiCall(fn () => $this->languageService->list());
        if (! $langResponse['ok']) {
            $this->maybeFlashDevError($langResponse);
        }
        $languages    = $this->extractItems($langResponse) ?: [];
        $form         = $this->normalizeForm($this->extractData($formResponse));
        $form['usages'] = $this->prepareFormUsages($form['usages'] ?? []);

        return $this->render('cms/forms/show', [
            'title'     => lang('Forms.show_title'),
            'form'      => $form,
            'languages' => $languages,
        ]);
    }

    public function create(): string
    {
        $langResponse = $this->safeApiCall(fn () => $this->languageService->list());
        if (! $langResponse['ok']) {
            $this->maybeFlashDevError($langResponse);
        }
        $languages    = $this->extractItems($langResponse) ?: [];
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'submit_label', 'description', 'success_message', 'error_message'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId, 'translations')
            : [];

        return $this->render('cms/forms/create', [
            'title'            => lang('Forms.create_title'),
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        $data = (array) $this->request->getPost();

        $translations = $this->extractTranslationsFromPost($data);

        $payload = [
            'form_key'              => $this->request->getPost('form_key'),
            'is_active'             => (bool) $this->request->getPost('is_active'),
            'has_captcha'           => (bool) $this->request->getPost('has_captcha'),
            'notify_email'          => $this->request->getPost('notify_email') ?: null,
            'autoreply_enabled'     => (bool) $this->request->getPost('autoreply_enabled'),
            'autoreply_email_field' => $this->request->getPost('autoreply_email_field') ?: null,
            'translations'          => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->formService->create($payload));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Forms.create_failed'), route_to('admin.cms.forms.create'));
        }

        $form = $this->extractData($response);
        $id   = $form['id'] ?? null;

        return redirect()
            ->to(route_to('admin.cms.forms.edit', $id))
            ->with('success', lang('Forms.create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $formResponse = $this->safeApiCall(fn () => $this->formService->get($id));

        if (! $formResponse['ok']) {
            $this->maybeFlashDevError($formResponse);

            return $this->withError(lang('Forms.not_found'), route_to('admin.cms.forms'));
        }

        $langResponse = $this->safeApiCall(fn () => $this->languageService->list());
        if (! $langResponse['ok']) {
            $this->maybeFlashDevError($langResponse);
        }
        $languages    = $this->extractItems($langResponse) ?: [];
        $form         = $this->normalizeForm($this->extractData($formResponse));
        $form['usages'] = $this->prepareFormUsages($form['usages'] ?? []);
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'submit_label', 'description', 'success_message', 'error_message'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId, 'translations')
            : [];

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        return $this->render('cms/forms/edit', [
            'title'            => lang('Forms.edit_title'),
            'form'             => $form,
            'languages'        => $languages,
            'focusLangId'      => $focusLangId,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
            'returnTo' => $this->incomingReturnTo(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        $data         = (array) $this->request->getPost();
        $translations = $this->extractTranslationsFromPost($data);

        $payload = [
            'is_active'             => (bool) $this->request->getPost('is_active'),
            'has_captcha'           => (bool) $this->request->getPost('has_captcha'),
            'notify_email'          => $this->request->getPost('notify_email') ?: null,
            'autoreply_enabled'     => (bool) $this->request->getPost('autoreply_enabled'),
            'autoreply_email_field' => $this->request->getPost('autoreply_email_field') ?: null,
            'translations'          => $translations,
        ];

        $response = $this->safeApiCall(fn () => $this->formService->update($id, $payload));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Forms.update_failed'), route_to('admin.cms.forms.edit', $id));
        }

        return redirect()
            ->to($this->resolveReturnUrl(route_to('admin.cms.forms.edit', $id)))
            ->with('success', lang('Forms.update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->formService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Forms.delete_failed'), route_to('admin.cms.forms'));
        }

        return redirect()
            ->to(route_to('admin.cms.forms'))
            ->with('success', lang('Forms.delete_success'));
    }

    // ── AJAX field management (called from the field builder in edit.php) ───

    public function storeField(string $formId): ResponseInterface
    {
        $data     = $this->jsonOrPost();
        $response = $this->safeApiCall(fn () => $this->formService->createField($formId, $data));

        return $this->ajaxApiResponse($response);
    }

    public function updateField(string $formId, string $fieldId): ResponseInterface
    {
        $data     = $this->jsonOrPost();
        $response = $this->safeApiCall(fn () => $this->formService->updateField($formId, $fieldId, $data));

        return $this->ajaxApiResponse($response);
    }

    public function deleteField(string $formId, string $fieldId): ResponseInterface
    {
        $response = $this->safeApiCall(fn () => $this->formService->deleteField($formId, $fieldId));

        return $this->ajaxApiResponse($response);
    }

    public function reorderFields(string $formId): ResponseInterface
    {
        $data       = $this->jsonOrPost();
        $orderedIds = array_values(array_map('intval', is_array($data['ordered_ids'] ?? null) ? $data['ordered_ids'] : []));
        $response   = $this->safeApiCall(fn () => $this->formService->reorderFields($formId, $orderedIds));

        return $this->ajaxApiResponse($response);
    }

    /**
     * @param mixed $usages
     * @return list<array<string, mixed>>
     */
    private function prepareFormUsages(mixed $usages): array
    {
        if (! is_array($usages) || $usages === []) {
            return [];
        }

        return array_values(array_map(function (array $usage): array {
            $context = is_array($usage['context'] ?? null) ? (array) $usage['context'] : [];
            $ownerType = (string) ($context['owner_type'] ?? '');
            $ownerId   = (int) ($context['owner_id'] ?? 0);
            $resourceId = (int) ($usage['resource_id'] ?? 0);

            return array_merge($usage, [
                'edit_url' => $this->resolveUsageEditUrl($ownerType, $ownerId, $resourceId),
            ]);
        }, $usages));
    }

    private function resolveUsageEditUrl(string $ownerType, int $ownerId, int $resourceId): ?string
    {
        if ($ownerType === 'page' && $ownerId > 0) {
            return site_url('admin/cms/pages/' . $ownerId . '/blocks/' . $resourceId . '/edit');
        }

        if ($ownerType === 'entry' && $ownerId > 0) {
            return site_url('admin/cms/entries/' . $ownerId . '/blocks/' . $resourceId . '/edit');
        }

        return null;
    }

    /**
     * @param array<string, mixed> $response
     */
    private function ajaxApiResponse(array $response): ResponseInterface
    {
        $ok     = (bool) ($response['ok'] ?? false);
        $status = (int) ($response['status'] ?? 0);

        $devErrorHtml = '';
        if (! $ok) {
            $this->maybeFlashDevError($response);
            $devErrorHtml = $this->renderDevApiErrorPanel($response);
        }

        if ($status < 100) {
            $status = $ok ? 200 : 422;
        }

        if (! $ok && $status < 400) {
            $status = 422;
        }

        return $this->response
            ->setStatusCode($status)
            ->setJSON([
                'ok'          => $ok,
                'data'        => $this->extractData($response),
                'messages'    => $response['messages'] ?? [],
                'fieldErrors' => $response['fieldErrors'] ?? [],
                'devErrorHtml' => $devErrorHtml,
            ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function jsonOrPost(): array
    {
        if ($this->request instanceof \CodeIgniter\HTTP\IncomingRequest) {
            $json = $this->request->getJSON(true);
            if (is_array($json)) {
                return $json;
            }
        }

        return (array) $this->request->getPost();
    }

    /**
     * @param array<string, mixed> $form
     * @return array<string, mixed>
     */
    private function normalizeForm(array $form): array
    {
        $form['translations'] = $this->onlyArrayItems($form['translations'] ?? []);
        $form['fields']       = array_map(
            fn (array $field): array => array_merge($field, [
                'translations' => $this->onlyArrayItems($field['translations'] ?? []),
            ]),
            $this->onlyArrayItems($form['fields'] ?? [])
        );

        return $form;
    }

    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    private function onlyArrayItems(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, static fn (mixed $item): bool => is_array($item)));
    }

    /**
     * Extract language-keyed translations from POST data.
     * Expects keys like translations[1][name], translations[1][submit_label], etc.
     *
     * @param array<string, mixed> $post
     * @return list<array<string, mixed>>
     */
    private function extractTranslationsFromPost(array $post): array
    {
        $raw = $post['translations'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $result = [];
        foreach ($raw as $key => $trans) {
            if (! is_array($trans)) {
                continue;
            }
            $langId = isset($trans['language_id']) ? (int) $trans['language_id'] : (int) $key;
            $result[] = array_merge(['language_id' => $langId], $trans);
        }

        return $result;
    }
}
