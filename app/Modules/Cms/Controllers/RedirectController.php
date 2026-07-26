<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Requests\RedirectStoreRequest;
use App\Modules\Cms\Requests\RedirectUpdateRequest;
use App\Modules\Cms\Services\RedirectApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RedirectController extends BaseWebController
{
    protected RedirectApiService $redirectService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->redirectService = service('redirectApiService');
    }

    public function index(): string
    {
        return $this->render('cms/redirects/index', [
            'title'        => lang('Redirects.redirects_title'),
            'limitOptions' => [10, 25, 50, 100],

        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            [],
            ['name', 'created_at'],
            fn (array $params) => $this->redirectService->list($params),
        );
    }

    public function show(string $id): string
    {
        $response = $this->safeApiCall(fn () => $this->redirectService->get($id));

        if (! $response['ok']) {
            return $this->render('cms/redirects/show', [
                'title' => lang('Redirects.redirects_details'),
                'redirect' => [],
                'error' => $this->firstMessage($response, lang('Redirects.redirects_not_found')),

            ]);
        }

        return $this->render('cms/redirects/show', [
            'title' => lang('Redirects.redirects_details'),
            'redirect' => $this->extractData($response),

        ]);
    }

    public function create(): string
    {
        return $this->render('cms/redirects/create', [
            'title' => lang('Redirects.redirects_create'),

        ]);
    }

    public function store(): RedirectResponse
    {
        /** @var RedirectStoreRequest $request */
        $request = service('formRequest', RedirectStoreRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->redirectService->create($request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Redirects.redirects_create_failed'));
        }

        return redirect()->to(route_to('admin.cms.redirects'))->with('success', lang('Redirects.redirects_create_success'));
    }

    public function edit(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->redirectService->get($id));
        if (! $response['ok']) {
            return $this->withError(lang('Redirects.redirects_not_found'), route_to('admin.cms.redirects'));
        }

        return $this->render('cms/redirects/edit', [
            'title' => lang('Redirects.redirects_edit'),
            'item'  => $this->extractData($response),

        ]);
    }

    public function update(string $id): RedirectResponse
    {
        /** @var RedirectUpdateRequest $request */
        $request = service('formRequest', RedirectUpdateRequest::class, false);
        $invalid = $this->validateRequest($request);
        if ($invalid !== null) {
            return $invalid;
        }

        $response = $this->safeApiCall(fn () => $this->redirectService->update($id, $request->payload()));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Redirects.redirects_update_failed'));
        }

        return redirect()->to(route_to('admin.cms.redirects'))->with('success', lang('Redirects.redirects_update_success'));
    }

    public function delete(string $id): RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->redirectService->delete($id));

        if (! $response['ok']) {
            return $this->failApi($response, lang('Redirects.redirects_delete_failed'), route_to('admin.cms.redirects'), false);
        }

        return redirect()->to(route_to('admin.cms.redirects'))->with('success', lang('Redirects.redirects_delete_success'));
    }


    public function exportCsv(): ResponseInterface
    {
        $getParams = $this->request->getGet();
        $filters = is_array($getParams) ? $getParams : [];
        $response = $this->safeApiCall(fn () => $this->redirectService->exportCsv($filters));
        if (! $response['ok']) {
            return $this->response->setStatusCode(500)->setBody(lang('Redirects.redirects_csv_export_failed'));
        }

        $rows = $this->extractItems($response);
        $columns = ['old_path', 'new_url', 'redirect_type', 'is_active', 'note'];
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            return $this->response->setStatusCode(500)->setBody(lang('Redirects.redirects_csv_export_failed'));
        }
        fputcsv($stream, $columns);

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $line = [];
            foreach ($columns as $column) {
                $line[] = (string) ($row[$column] ?? '');
            }

            fputcsv($stream, $line);
        }

        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="redirects.csv"')
            ->setBody($csv);
    }

    public function importCsv(): RedirectResponse
    {
        $file = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest ? $this->request->getFile('csv_file') : null;
        if ($file === null || ! $file->isValid()) {
            return $this->withError(lang('Redirects.redirects_csv_invalid_file'), route_to('admin.cms.redirects'));
        }

        $handle = fopen($file->getTempName(), 'r');
        if ($handle === false) {
            return $this->withError(lang('Redirects.redirects_csv_invalid_file'), route_to('admin.cms.redirects'));
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            return $this->withError(lang('Redirects.redirects_csv_invalid_file'), route_to('admin.cms.redirects'));
        }

        $columns = ['old_path', 'new_url', 'redirect_type', 'is_active', 'note'];
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $assoc = [];
            foreach ($headers as $index => $header) {
                if (! is_string($header) || ! isset($columns[$index])) {
                    continue;
                }

                $assoc[$columns[$index]] = $row[$index] ?? '';
            }

            if (isset($assoc['old_path']) && isset($assoc['new_url']) && isset($assoc['redirect_type'])) {
                // Validate fields
                $oldPath = trim((string)$assoc['old_path']);
                if (strpos($oldPath, '/') !== 0) {
                    fclose($handle);
                    return $this->withError(lang('Redirects.redirects_old_path_slash'), route_to('admin.cms.redirects'));
                }

                $redirectType = trim((string)$assoc['redirect_type']);
                if ($redirectType !== '301' && $redirectType !== '302') {
                    fclose($handle);
                    return $this->withError(lang('Redirects.redirects_invalid_type'), route_to('admin.cms.redirects'));
                }

                $assoc['is_active'] = filter_var($assoc['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $rows[] = $assoc;
            }
        }

        fclose($handle);

        $response = $this->safeApiCall(fn () => $this->redirectService->importCsv($rows));
        if (! $response['ok']) {
            return $this->failApi($response, lang('Redirects.redirects_csv_import_failed'), route_to('admin.cms.redirects'), false);
        }

        return redirect()->to(route_to('admin.cms.redirects'))->with('success', lang('Redirects.redirects_csv_import_success'));
    }



}
