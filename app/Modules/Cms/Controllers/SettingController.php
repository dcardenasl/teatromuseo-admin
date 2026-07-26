<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\SettingStoreRequest;
use App\Modules\Cms\Requests\SettingUpdateRequest;
use App\Modules\Cms\Services\SettingApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class SettingController extends BaseWebController
{
    protected SettingApiService $settingService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->settingService = service('settingApiService');
    }

    private function requireRead(): ?RedirectResponse
    {
        if (! has_permission('cms.settings.read')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.settings.write')) {
            return redirect()->to(route_to('admin.cms.settings'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function index(): string|RedirectResponse
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $deny;
        }

        $languages = $this->getLanguages();

        return $this->render('cms/settings/index', [
            'title'        => lang('Settings.settings_title'),
            'limitOptions' => [10, 25, 50, 100],
            'languages'    => $languages,
        ]);
    }

    public function data(): ResponseInterface
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => lang('App.access_denied'),
            ])->setStatusCode(403);
        }

        return $this->tableDataResponse(
            [],
            ['setting_key', 'setting_value', 'setting_type', 'setting_group', 'is_translatable', 'created_at'],
            fn (array $params) => $this->settingService->list([...$params, 'include_translations' => 1]),
        );
    }

    public function show(string $id): string|RedirectResponse
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $deny;
        }
        $response = $this->safeApiCall(fn () => $this->settingService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/settings/show', [
                'title' => lang('Settings.settings_details'),
                'setting' => [],
                'languages' => [],
                'error' => $this->firstMessage($response, lang('Settings.settings_not_found')),

            ]);
        }

        $languages = $this->getLanguages();

        return $this->render('cms/settings/show', [
            'title' => lang('Settings.settings_details'),
            'setting' => $this->extractData($response),
            'languages' => $languages,

        ]);
    }

    public function create(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);

        return $this->render('cms/settings/create', [
            'title'     => lang('Settings.settings_create'),
            'languages' => $languages,
            'baseLanguageId' => $languageContext['defaultLangId'],
        ]);
    }

    public function store(): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        /** @var SettingStoreRequest $request */
        $request = service('formRequest', SettingStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $languages = $this->getLanguages();
        $request->setLanguages($languages);
        $request->setBaseLanguageId($this->resolveLanguageContext($languages)['defaultLangId']);

        $response = $this->safeApiCall(fn () => $this->settingService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Settings.settings_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.settings'))->with('success', lang('Settings.settings_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->settingService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Settings.settings_not_found'), route_to('admin.cms.settings'));
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        return $this->render('cms/settings/edit', [
            'title'     => lang('Settings.settings_edit'),
            'item'      => $this->extractData($response),
            'languages' => $languages,
            'focusLangId' => $focusLangId,
            'baseLanguageId' => $languageContext['defaultLangId'],
            'returnTo' => $this->incomingReturnTo(),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        /** @var SettingUpdateRequest $request */
        $request = service('formRequest', SettingUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $languages = $this->getLanguages();
        $request->setLanguages($languages);
        $request->setBaseLanguageId($this->resolveLanguageContext($languages)['defaultLangId']);

        $response = $this->safeApiCall(fn () => $this->settingService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Settings.settings_update_failed'));
        }

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.settings')))->with('success', lang('Settings.settings_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $response = $this->safeApiCall(fn () => $this->settingService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Settings.settings_delete_failed'), route_to('admin.cms.settings'), false);
        }

        return redirect()->to(route_to('admin.cms.settings'))->with('success', lang('Settings.settings_delete_success'));
    }

    /**
     * @return array<string, mixed>
     */
    private function getLanguages(): array
    {
        $response = $this->safeApiCall(fn () => service('languageApiService')->list(['limit' => 100, 'is_active' => true]));
        $this->maybeFlashDevError($response);
        return $this->extractItems($response);
    }
}
