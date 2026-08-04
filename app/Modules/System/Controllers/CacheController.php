<?php

declare(strict_types=1);

namespace App\Modules\System\Controllers;

use App\Controllers\BaseWebController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

final class CacheController extends BaseWebController
{
    /** @var list<string> */
    private const PUBLIC_SCOPES = [
        'settings', 'menus', 'pages', 'collections', 'entries', 'taxonomies',
        'events', 'event_types', 'categories', 'techniques', 'collection_items',
        'redirects', 'forms',
    ];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
    }

    public function index(): string
    {
        $result = $this->safeApiCall(static fn (): array => service('publicSiteCacheInvalidator')->status());
        $status = is_array($result['data'] ?? null) ? $result['data'] : [];

        return $this->render('system/cache', [
            'title' => lang('System.cache_title'),
            'status' => $status,
            'statusError' => ! ($result['ok'] ?? false)
                ? ((string) ($result['message'] ?? lang('System.cache_status_unavailable')))
                : null,
            'scopes' => self::PUBLIC_SCOPES,
        ]);
    }

    public function invalidate(): RedirectResponse
    {
        $result = $this->safeApiCall(
            static fn (): array => service('publicSiteCacheInvalidator')->invalidateWithResult(
                self::PUBLIC_SCOPES,
                'admin_manual',
            )
        );

        if (($result['ok'] ?? false) === true) {
            log_message('info', '[PublicSiteCache] Manual invalidation requested by user {user_id}.', [
                'user_id' => (string) (current_user_id() ?? 'unknown'),
            ]);

            return redirect()->to(route_to('admin.system.cache'))->with(
                'success',
                lang('System.cache_invalidated', [
                    'deleted' => (string) ($result['deleted'] ?? 0),
                ])
            );
        }

        return redirect()->to(route_to('admin.system.cache'))->with(
            'error',
            lang('System.cache_invalidation_failed')
        );
    }
}
