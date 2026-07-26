<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\SiteIdentityUpdateRequest;
use App\Modules\Cms\Services\SettingApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class SiteIdentityController extends BaseWebController
{
    protected SettingApiService $settingService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->settingService = service('settingApiService');
    }

    private function requireWrite(): ?RedirectResponse
    {
        if (! has_permission('cms.settings.write')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function show(): string|RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }
        helper('cms_settings');

        $response = $this->safeApiCall(fn () => $this->settingService->getByGroup('identity'));
        if (! ($response['ok'] ?? false)) {
            return $this->failApi(
                $response,
                lang('SiteIdentity.update_failed'),
                route_to('admin.cms.site_identity'),
                false
            );
        }
        $items    = $this->extractItems($response);

        $langsRes       = $this->safeApiCall(fn () => service('languageApiService')->list(['is_active' => 1]));
        $languages      = array_values($langsRes['ok'] ? $this->extractItems($langsRes) : []);
        $languageContext = $this->resolveLanguageContext($languages);
        $settingsMap    = $this->indexSettingsByKey($items);
        $sortedSettings = $this->sortSettingsByOrder($settingsMap);
        [$contentSettings, $assetSettings] = $this->splitSettingsByInputType($sortedSettings);
        $translationPanel = cms_settings_build_translation_panel($contentSettings, $languages, $languageContext['defaultLangId']);

        return $this->render('cms/site-identity/show', [
            'title'            => lang('SiteIdentity.page_title'),
            'contentSettings'  => $contentSettings,
            'assetSettings'    => $assetSettings,
            'translationPanel' => $translationPanel,
        ]);
    }

    public function update(): RedirectResponse
    {
        $deny = $this->requireWrite();
        if ($deny !== null) {
            return $deny;
        }

        $identityResponse = $this->settingService->getByGroup('identity');
        $items            = $this->extractItems($identityResponse);

        $langsRes       = $this->safeApiCall(fn () => service('languageApiService')->list(['is_active' => 1]));
        $languages      = array_values($langsRes['ok'] ? $this->extractItems($langsRes) : []);
        $languageContext = $this->resolveLanguageContext($languages);

        /** @var SiteIdentityUpdateRequest $formRequest */
        $formRequest = service('formRequest', SiteIdentityUpdateRequest::class, false);
        $formRequest->setIdentitySettings($items);
        $formRequest->setLanguages($languages);
        $formRequest->setBaseLanguageId($languageContext['defaultLangId']);

        $invalid = $this->validateRequest($formRequest);
        if ($invalid !== null) {
            return $invalid;
        }

        /** @var array<string, int> $idMap keyed by setting_key → setting id */
        $idMap = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['setting_key'], $item['id'])
                && is_string($item['setting_key']) && is_numeric($item['id'])) {
                $idMap[(string) $item['setting_key']] = (int) $item['id'];
            }
        }

        $payloads = $formRequest->payload();
        $batchUpdates = [];

        foreach ($payloads as $settingKey => $updateData) {
            if (!isset($idMap[$settingKey])) {
                continue;
            }

            // The form contains every identity field, but an update must be
            // sent only when that field actually changed. Apart from reducing
            // load, this prevents an unrelated stale Domain API request from
            // blocking the whole save operation.
            $currentSetting = $this->findSettingByKey($items, (string) $settingKey);
            if ($currentSetting !== null && $this->settingPayloadIsUnchanged($currentSetting, $updateData)) {
                continue;
            }

            $batchUpdates[] = ['id' => $idMap[$settingKey], 'payload' => $updateData];
        }

        $result = $batchUpdates === []
            ? ['ok' => true]
            : $this->safeApiCall(fn () => $this->settingService->batchUpdate($batchUpdates));
        if (! ($result['ok'] ?? false)) {
            $this->maybeFlashDevError($result);
            return redirect()->to(route_to('admin.cms.site_identity') . '?saved=error')
                ->with('error', $this->firstMessage($result, lang('SiteIdentity.update_failed')));
        }

        return redirect()->to(route_to('admin.cms.site_identity') . '?saved=success')
            ->with('success', lang('SiteIdentity.update_success'));
    }

    /**
     * @param array<string, mixed> $items
     * @return array<string, mixed>|null
     */
    private function findSettingByKey(array $items, string $settingKey): ?array
    {
        foreach ($items as $item) {
            if (is_array($item) && (string) ($item['setting_key'] ?? '') === $settingKey) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $setting
     * @param array<string, mixed> $payload
     */
    private function settingPayloadIsUnchanged(array $setting, array $payload): bool
    {
        // Translation writes are intentionally left to the API because their
        // persisted values are separate records and are not always included
        // in the group response in the same shape.
        if (isset($payload['translations'])) {
            return false;
        }

        if ((string) ($setting['setting_value'] ?? '') !== (string) ($payload['setting_value'] ?? '')) {
            return false;
        }

        if (!array_key_exists('setting_meta', $payload)) {
            return true;
        }

        return $this->normalizeSettingMeta($setting['setting_meta'] ?? null)
            === $this->normalizeSettingMeta($payload['setting_meta']);
    }

    /** @return array<string, string> */
    private function normalizeSettingMeta(mixed $meta): array
    {
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $meta = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($meta)) {
            return [];
        }

        return [
            'url' => trim((string) ($meta['url'] ?? '')),
            'mime_type' => trim((string) ($meta['mime_type'] ?? '')),
        ];
    }

    /**
     * @param array<int|string, mixed> $items
     * @return array<string, array<string, mixed>>
     */
    private function indexSettingsByKey(array $items): array
    {
        $settingsMap = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['setting_key']) || ! is_string($item['setting_key'])) {
                continue;
            }

            $settingsMap[$item['setting_key']] = $item;
        }

        return $settingsMap;
    }

    /**
     * @param array<string, array<string, mixed>> $settingsMap
     * @return array<int, array<string, mixed>>
     */
    private function sortSettingsByOrder(array $settingsMap): array
    {
        $sortedSettings = array_values($settingsMap);
        usort($sortedSettings, static fn (array $a, array $b): int => (int) ($a['sort_order'] ?? 0) <=> (int) ($b['sort_order'] ?? 0));

        return $sortedSettings;
    }

    /**
     * @param array<int, array<string, mixed>> $sortedSettings
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function splitSettingsByInputType(array $sortedSettings): array
    {
        $assetSettings = [];
        $contentSettings = [];

        foreach ($sortedSettings as $setting) {
            $inputType = (string) ($setting['input_type'] ?? 'text');
            if (in_array($inputType, ['image', 'file'], true)) {
                $assetSettings[] = $setting;
                continue;
            }

            $contentSettings[] = $setting;
        }

        return [$contentSettings, $assetSettings];
    }
}
