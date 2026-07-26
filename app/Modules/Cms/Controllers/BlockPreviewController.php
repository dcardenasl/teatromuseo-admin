<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use CodeIgniter\HTTP\ResponseInterface;

class BlockPreviewController extends BaseWebController
{
    public function preview(): ResponseInterface
    {
        $blockKeyRaw = $this->request->getPost('block_key');
        $configRaw   = $this->request->getPost('block_config');
        $dataRaw     = $this->request->getPost('block_data');
        $previewModeRaw = $this->request->getPost('preview_mode');
        $blockKey    = is_scalar($blockKeyRaw) ? (string) $blockKeyRaw : '';
        $configRaw   = is_scalar($configRaw) ? (string) $configRaw : '';
        $dataRaw     = is_scalar($dataRaw) ? (string) $dataRaw : '';
        $previewMode = is_scalar($previewModeRaw) ? (string) $previewModeRaw : 'sample';

        $config = json_decode($configRaw ?: '{}', true) ?? [];
        $data   = json_decode($dataRaw ?: '{}', true) ?? [];

        $html = null;

        // Try proxying request to public website's BlockPreviewController
        $publicSiteUrl = rtrim((string) env('PUBLIC_SITE_URL'), '/');
        if ($publicSiteUrl !== '') {
            try {
                $client = \Config\Services::curlrequest();
                $response = $client->post($publicSiteUrl . '/blocks/preview', [
                    'form_params' => [
                        'block_key'    => $blockKey,
                        'block_config' => $configRaw,
                        'block_data'   => $dataRaw,
                        'preview_mode' => $previewMode,
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'http_errors' => false,
                    'timeout'     => 3,
                ]);

                if ($response->getStatusCode() === 200) {
                    $responseBody = $response->getBody();
                    $resJson = $responseBody !== null ? json_decode($responseBody, true) : null;
                    if (is_array($resJson) && isset($resJson['html'])) {
                        $html = (string) $resJson['html'];
                    }
                }
            } catch (\Throwable $e) {
                log_message('warning', '[BlockPreviewController] Public site preview request failed: ' . $e->getMessage() . '. Falling back to local preview.');
            }
        }

        // Fallback to local preview template if public site is not accessible
        if ($html === null) {
            $safeKey  = preg_replace('/[^a-z0-9_]/', '', strtolower($blockKey));
            $viewPath = "cms/block_types/previews/{$safeKey}";
            if (! is_file(APPPATH . "Views/{$viewPath}.php")) {
                $viewPath = 'cms/block_types/previews/unknown';
            }

            $html = view($viewPath, compact('config', 'data', 'blockKey'));
        }

        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode(['html' => $html]) ?: '{}');
    }
}
