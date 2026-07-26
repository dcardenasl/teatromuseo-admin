<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\TagStoreRequest;
use App\Modules\Cms\Requests\TagUpdateRequest;
use App\Modules\Cms\Services\TagApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TagController extends BaseWebController
{
    protected TagApiService $tagService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->tagService = service('tagApiService');
    }

    public function index(): string
    {
        return $this->render('cms/tags/index', [
            'title'        => lang('Tags.tags_title'),
            'limitOptions' => [10, 25, 50, 100],
            'languages'    => $this->getLanguages(),
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->tagService->list([...$params, 'include_translations' => 1]),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->tagService->get($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->render('cms/tags/show', [
                'title' => lang('Tags.tags_details'),
                'tag' => [],
                'error' => $this->firstMessage($response, lang('Tags.tags_not_found')),

            ]);
        }

        return $this->render('cms/tags/show', [
            'title' => lang('Tags.tags_details'),
            'tag' => $this->extractData($response),
            'languages' => $this->getLanguages(),

        ]);
    }

    public function create(): string
    {
        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'slug'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        return $this->render('cms/tags/create', [
            'title'            => lang('Tags.tags_create'),
            'languages'        => $languages,
            'defaultLangId'    => $languageContext['defaultLangId'],
            'defaultLangCode'  => $languageContext['defaultLangCode'],
            'defaultLangIndex' => $languageContext['defaultLangIndex'],
            'translateTargets' => $translateTargets,
        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var TagStoreRequest $request */
        $request = service('formRequest', TagStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->tagService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Tags.tags_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.tags'))->with('success', lang('Tags.tags_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->tagService->get($id));
        if (! $response['ok']) {
            $this->maybeFlashDevError($response);
            return $this->withError(lang('Tags.tags_not_found'), route_to('admin.cms.tags'));
        }

        $languages = $this->getLanguages();
        $languageContext = $this->resolveLanguageContext($languages);
        $defaultLangId = $languageContext['defaultLangId'];
        $fieldMap = ['name', 'slug'];
        $translateTargets = ($defaultLangId > 0 && !empty($languages))
            ? $this->buildTranslateTargets($languages, $fieldMap, $defaultLangId)
            : [];

        $focusLangRaw = $this->request->getGet('focus_lang');
        $focusLangId  = ($focusLangRaw !== null && is_scalar($focusLangRaw) && (int) $focusLangRaw > 0)
            ? (int) $focusLangRaw
            : 0;

        return $this->render('cms/tags/edit', [
            'title'            => lang('Tags.tags_edit'),
            'item'             => $this->extractData($response),
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
        /** @var TagUpdateRequest $request */
        $request = service('formRequest', TagUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->tagService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Tags.tags_update_failed'));
        }

        return redirect()->to($this->resolveReturnUrl(route_to('admin.cms.tags')))->with('success', lang('Tags.tags_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->tagService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Tags.tags_delete_failed'), route_to('admin.cms.tags'), false);
        }

        return redirect()->to(route_to('admin.cms.tags'))->with('success', lang('Tags.tags_delete_success'));
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
