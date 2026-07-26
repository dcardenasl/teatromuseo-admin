<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\FileTranslationApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class FileTranslationController extends BaseWebController
{
    protected FileTranslationApiService $fileTranslationService;
    protected LanguageApiService $languageService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->fileTranslationService = service('fileTranslationApiService');
        $this->languageService        = service('languageApiService');
    }

    public function edit(string $fileId): string|RedirectResponse
    {
        if (! has_permission('cms.pages.write')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }

        $numericFileId = (int) $fileId;

        $transResponse = $this->fileTranslationService->getForFile($numericFileId);
        $langResponse  = $this->languageService->list(['per_page' => 100]);

        $translations = [];
        if (isset($transResponse['data']) && is_array($transResponse['data'])) {
            foreach ($transResponse['data'] as $t) {
                if (is_array($t) && isset($t['language_id'])) {
                    $translations[(int) $t['language_id']] = $t;
                }
            }
        }

        $languages = [];
        if (isset($langResponse['data']) && is_array($langResponse['data'])) {
            $languages = array_values(array_filter($langResponse['data'], 'is_array'));
        }

        return $this->render('cms/file_translations/edit', [
            'title'        => lang('FileTranslations.page_title'),
            'fileId'       => $numericFileId,
            'languages'    => $languages,
            'translations' => $translations,
        ]);
    }

    public function update(string $fileId): RedirectResponse
    {
        if (! has_permission('cms.pages.write')) {
            return redirect()->to(route_to('dashboard'))->with('error', lang('App.access_denied'));
        }

        $numericFileId = (int) $fileId;

        /** @var list<array<string, mixed>> $submitted */
        $submitted = $this->request->getPost('translations');
        if (! is_array($submitted)) {
            return redirect()->to(route_to('admin.cms.file_translations.edit', $fileId))->with('error', lang('App.invalid_request'));
        }

        $failed = false;

        foreach ($submitted as $row) {
            if (! is_array($row)) {
                continue;
            }

            $langId = (int) ($row['language_id'] ?? 0);
            if ($langId === 0) {
                continue;
            }

            $payload = [
                'file_id'     => $numericFileId,
                'language_id' => $langId,
                'alt_text'    => isset($row['alt_text']) ? (string) $row['alt_text'] : null,
                'caption'     => isset($row['caption']) ? (string) $row['caption'] : null,
                'title'       => isset($row['title']) ? (string) $row['title'] : null,
                'credit'      => isset($row['credit']) ? (string) $row['credit'] : null,
                'description' => isset($row['description']) ? (string) $row['description'] : null,
            ];

            $existingId = isset($row['existing_id']) && $row['existing_id'] !== '' ? (int) $row['existing_id'] : null;

            if ($existingId !== null) {
                $result = $this->fileTranslationService->updateForFile($numericFileId, $existingId, $payload);
            } else {
                $result = $this->fileTranslationService->createForFile($numericFileId, $payload);
            }

            if (! ($result['ok'] ?? false)) {
                $failed = true;
            }
        }

        if ($failed) {
            return redirect()->to(route_to('admin.cms.file_translations.edit', $fileId))->with('error', lang('FileTranslations.update_failed'));
        }

        return redirect()->to(route_to('admin.cms.file_translations.edit', $fileId))->with('success', lang('FileTranslations.update_success'));
    }
}
