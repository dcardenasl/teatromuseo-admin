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

        $ch = curl_init($url);
        if ($ch === false) {
            return $this->response->setJSON(['error' => 'Translation service unavailable.'])->setStatusCode(503);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_TIMEOUT        => 10,
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

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
