<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Cms\Services\FormSubmissionApiService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class FormSubmissionController extends BaseWebController
{
    protected FormSubmissionApiService $submissionService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->submissionService = service('formSubmissionApiService');
    }

    public function index(): string
    {
        $rawStatus = $this->request->getGet('status');
        $status    = is_string($rawStatus) ? $rawStatus : '';

        // Load count badges for tab navigation
        $countsResponse = $this->safeApiCall(fn () => $this->submissionService->counts());
        if (! $countsResponse['ok']) {
            $this->maybeFlashDevError($countsResponse);
        }
        $counts = $this->extractData($countsResponse) ?: ['new' => 0, 'read' => 0, 'replied' => 0, 'spam' => 0, 'archived' => 0];

        return $this->render('cms/form_submissions/index', [
            'title'        => lang('FormSubmissions.title'),
            'limitOptions' => [10, 25, 50],
            'activeStatus' => $status,
            'counts'       => $counts,
        ]);
    }

    public function data(): ResponseInterface
    {
        return $this->tableDataResponse(
            ['status', 'form_key'],
            ['created_at'],
            fn (array $params) => $this->submissionService->list($params),
        );
    }

    public function show(string $id): string|RedirectResponse
    {
        $response = $this->safeApiCall(fn () => $this->submissionService->get($id));

        if (! $response['ok']) {
            $this->maybeFlashDevError($response);

            return $this->withError(
                lang('FormSubmissions.not_found'),
                route_to('admin.cms.form_submissions')
            );
        }

        $submission = $this->extractData($response);

        // Auto-mark as read when opened
        if (($submission['status'] ?? '') === 'new') {
            $readResponse = $this->safeApiCall(fn () => $this->submissionService->updateStatus($id, 'read'));
            if (! $readResponse['ok']) {
                $this->maybeFlashDevError($readResponse);
            }
            $submission['status'] = 'read';
        }

        return $this->render('cms/form_submissions/show', [
            'title'      => lang('FormSubmissions.detail_title'),
            'submission' => $submission,
        ]);
    }

    public function updateStatus(string $id): RedirectResponse
    {
        $rawStatus = $this->request->getPost('status');
        $status    = is_string($rawStatus) ? $rawStatus : '';

        $allowed = ['new', 'read', 'replied', 'spam', 'archived'];
        if (! in_array($status, $allowed, true)) {
            return $this->withError(lang('FormSubmissions.invalid_status'), route_to('admin.cms.form_submissions'));
        }

        $response = $this->safeApiCall(fn () => $this->submissionService->updateStatus($id, $status));

        if (! $response['ok']) {
            return $this->failApi($response, lang('FormSubmissions.update_failed'), route_to('admin.cms.form_submissions.show', $id));
        }

        return redirect()
            ->to(route_to('admin.cms.form_submissions.show', $id))
            ->with('success', lang('FormSubmissions.update_success'));
    }
}
