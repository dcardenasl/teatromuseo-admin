<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\LanguageApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TranslationAuditController extends BaseWebController
{
    protected TranslationAuditApiService $auditService;
    protected LanguageApiService $languageService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->auditService = service('translationAuditApiService');
        $this->languageService = service('languageApiService');
    }

    private function requireRead(): ?RedirectResponse
    {
        if (! has_permission('cms.languages.read')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }
        return null;
    }

    public function index(): string|RedirectResponse
    {
        $deny = $this->requireRead();
        if ($deny !== null) {
            return $deny;
        }

        // Get overall stats
        $statsRes = $this->safeApiCall(fn () => $this->auditService->getStats());
        if (! $statsRes['ok']) {
            $this->maybeFlashDevError($statsRes);
        }
        $stats = $statsRes['ok'] ? $this->extractData($statsRes) : [];

        // Get languages list for filter dropdown
        $langsRes = $this->safeApiCall(fn () => $this->languageService->list(['is_active' => 1]));
        if (! $langsRes['ok']) {
            $this->maybeFlashDevError($langsRes);
        }
        $languages = $langsRes['ok'] ? $this->extractItems($langsRes) : [];

        return $this->render('cms/translations/index', [
            'title'        => lang('Translations.audit_title') ?? 'Translation Audit',
            'stats'        => $stats,
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

        $langId = $this->request->getGet('language_id');
        $filters = [];
        if (is_scalar($langId) && $langId !== '') {
            $filters['language_id'] = (int) $langId;
        }
        foreach (['resource', 'status', 'search'] as $filter) {
            $value = $this->request->getGet($filter);
            if (is_string($value) && trim($value) !== '') {
                $filters[$filter] = trim($value);
            }
        }

        $response = $this->safeApiCall(fn () => $this->auditService->getReport($filters));

        $drawRaw = $this->request->getGet('draw');
        $draw    = is_scalar($drawRaw) ? (int) $drawRaw : 0;

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $this->firstMessage($response, lang('App.loadRetry'))
            ]);
        }

        $data = $this->extractData($response);

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ]);
    }
}
