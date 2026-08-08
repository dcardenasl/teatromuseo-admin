<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use CodeIgniter\HTTP\ResponseInterface;

class TranslateController extends BaseWebController
{
    private const GOOGLE_TRANSLATE_URL = 'https://translate.googleapis.com/translate_a/single';

    public function translate(): ResponseInterface
    {
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
}
