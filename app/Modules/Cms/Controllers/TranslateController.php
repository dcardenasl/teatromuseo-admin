<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use CodeIgniter\HTTP\ResponseInterface;

class TranslateController extends BaseWebController
{
    private const GOOGLE_TRANSLATE_URL = 'https://translate.googleapis.com/translate_a/single';

    /**
     * `translate()` is a generic on-demand-translation utility embedded in
     * content forms across CMS, Museum and Events (see the
     * `route_to('admin.cms.translate')` call sites in those modules'
     * views/JS) — including the CMS Wizard, whose `cms.entries.read` gate is
     * intentionally sufficient by itself to draft and translate content
     * before anything is saved (`cms.entries.write` is enforced separately
     * by the API on the actual create/update call). A single
     * `permission:<code>` route filter can't express "any read-or-write
     * permission belonging to one of these content modules", so the check
     * lives here instead — same OR-of-permissions pattern as
     * StructureWizardController::index()/config(). This intentionally
     * excludes unrelated broad-admin-section codes such as `iam.admin-access`
     * or `metrics.read`: holding one of those alone must not be enough to
     * reach this proxy (2026-08-12 audit finding).
     *
     * @var list<string>
     */
    private const REQUIRED_ANY_PERMISSION = [
        'cms.pages.read',
        'cms.pages.write',
        'cms.entries.read',
        'cms.entries.write',
        'cms.menus.read',
        'cms.menus.write',
        'cms.collections.read',
        'cms.collections.write',
        'cms.categories.read',
        'cms.categories.write',
        'cms.tags.read',
        'cms.tags.write',
        'cms.forms.read',
        'cms.forms.write',
        'cms.settings.read',
        'cms.settings.write',
        'catalog.category.read',
        'catalog.category.create',
        'catalog.category.update',
        'catalog.technique.read',
        'catalog.technique.create',
        'catalog.technique.update',
        'catalog.collectionItem.read',
        'catalog.collectionItem.create',
        'catalog.collectionItem.update',
        'event.event-types.read',
        'event.event-types.write',
    ];

    public function translate(): ResponseInterface
    {
        if (! $this->hasAnyContentPermission()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => lang('App.access_denied')]);
        }

        $textRaw       = $this->request->getGet('text');
        $sourceLangRaw = $this->request->getGet('source_lang');
        $targetLangRaw = $this->request->getGet('target_lang');
        $text       = is_scalar($textRaw) ? (string) $textRaw : '';
        $sourceLang = strtolower(is_scalar($sourceLangRaw) ? (string) $sourceLangRaw : 'auto');
        $targetLang = strtolower(is_scalar($targetLangRaw) ? (string) $targetLangRaw : '');

        if ($text === '' || $targetLang === '') {
            return $this->response->setJSON(['error' => 'Missing required parameters.'])->setStatusCode(400);
        }

        $url = self::GOOGLE_TRANSLATE_URL . '?' . http_build_query([
            'client' => 'gtx',
            'sl'     => $sourceLang,
            'tl'     => $targetLang,
            'dt'     => 't',
            'q'      => $text,
        ]);

        $client = \Config\Services::curlrequest([
            'timeout'     => 10,
            'http_errors' => false,
            'headers'     => [
                'User-Agent' => 'TeatroMuseoAdmin/1.0',
                'Accept'     => 'application/json',
            ],
        ]);

        try {
            $response = $client->get($url);
            $status   = $response->getStatusCode();
            $body     = $response->getBody();
        } catch (\Throwable $e) {
            return $this->response->setJSON(['error' => 'Translation service unavailable.'])->setStatusCode(503);
        }

        if (! is_string($body)) {
            return $this->response->setJSON(['error' => 'No response from translation service.'])->setStatusCode(503);
        }

        $json = json_decode($body, true);

        if ($status !== 200 || ! is_array($json) || empty($json[0]) || ! is_array($json[0])) {
            return $this->response->setJSON(['error' => 'Translation failed.'])->setStatusCode(502);
        }

        $translated = '';
        foreach ($json[0] as $sentence) {
            if (is_array($sentence) && isset($sentence[0])) {
                $translated .= $sentence[0];
            }
        }

        if ($translated === '') {
            return $this->response->setJSON(['error' => 'Empty translation response.'])->setStatusCode(502);
        }

        return $this->response->setJSON(['translated' => $translated]);
    }

    private function hasAnyContentPermission(): bool
    {
        foreach (self::REQUIRED_ANY_PERMISSION as $code) {
            if (has_permission($code)) {
                return true;
            }
        }

        return false;
    }
}
