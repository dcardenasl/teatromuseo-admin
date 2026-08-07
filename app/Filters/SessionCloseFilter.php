<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Releases the session's file lock immediately after the CSRF and auth
 * filters have finished reading/writing it, before the controller runs.
 *
 * CI4's session drivers (FileHandler in particular) hold an exclusive lock
 * on the session for the full lifetime of the request. Endpoints that fire
 * several requests in parallel from the same page (e.g. dashboard widgets
 * loading concurrently) end up serialized behind that lock instead of
 * running concurrently — on this host that queuing also surfaced as a race
 * where a concurrent filesize() stat on the session file failed outright
 * (writable/logs, 2026-08-07 19:43:57), which is what made a freshly
 * logged-in session look expired on the very next navigation.
 *
 * Scope this filter ONLY to read-only endpoints that never need to write to
 * session or set flash data after this point runs (see
 * Modules/Dashboard/Config/Routes.php's widget group). Applying it globally
 * would silently break flash messages / CSRF token rotation on normal pages.
 */
class SessionCloseFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        session()->close();

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
