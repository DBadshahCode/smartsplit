<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter
 *
 * Guards all routes in the 'auth' group.
 *
 * Handles:
 *  1. Unauthenticated access  — redirect to login (HTML) or 401 JSON (AJAX).
 *  2. Idle session timeout    — 2-hour inactivity limit enforced server-side.
 *  3. Role-change invalidation— the role stored in the session is re-verified
 *                               against the DB every 5 minutes so a role change
 *                               or account deletion takes effect quickly.
 */

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */

    /** Idle timeout in seconds (2 hours). */
    private const IDLE_TIMEOUT = 2 * 60 * 60;

    /** How often (seconds) to re-check role/existence against the DB. */
    private const ROLE_CHECK_INTERVAL = 5 * 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        // ── 1. Not logged in ───────────────────────────────────────────────
        if (! session()->get('is_logged_in')) {
            return $this->unauthenticated($request);
        }

        // ── 2. Idle session timeout ────────────────────────────────────────
        $lastActivity = (int) (session()->get('last_activity') ?? 0);
        if ($lastActivity > 0 && (time() - $lastActivity) > self::IDLE_TIMEOUT) {
            session()->destroy();
            return $this->unauthenticated($request, 'Your session has expired. Please sign in again.');
        }
        session()->set('last_activity', time());

        // ── 3. Periodic role / existence re-check against the DB ──────────
        // Catches: admin demotes a user, admin deletes a user — both take
        // effect within ROLE_CHECK_INTERVAL seconds without requiring logout.
        $lastCheck = (int) (session()->get('role_checked_at') ?? 0);
        if ((time() - $lastCheck) > self::ROLE_CHECK_INTERVAL) {
            $userId = (int) session()->get('user_id');
            /** @var \App\Models\User $userModel */
            $userModel = model(\App\Models\User::class);
            /** @var \App\Entities\User|null $user */
            $user = $userModel->find($userId);

            if (! ($user instanceof \App\Entities\User)) {
                // Account was deleted.
                session()->destroy();
                return $this->unauthenticated($request, 'Your account no longer exists.');
            }

            // Refresh role in the session in case an admin changed it.
            session()->set('role', $user->role);
            session()->set('role_checked_at', time());
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }

    // ── Helper: respond appropriately to unauthenticated requests ─────────
    private function unauthenticated(RequestInterface $request, string $message = ''): ResponseInterface
    {
        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $isAjax = $request->isAJAX()
            || str_contains((string) $request->getHeaderLine('Accept'), 'application/json');

        if ($isAjax) {
            // SS.Table and all $.ajax / $.get calls receive a clean 401 JSON
            // response instead of the login-page HTML. The front-end JS below
            // intercepts this globally and redirects to the login page.
            return response()
                ->setJSON([
                    'status'  => 'unauthenticated',
                    'message' => $message ?: 'Session expired. Please sign in again.',
                ])
                ->setStatusCode(401);
        }

        if ($message) {
            session()->setFlashdata('error', $message);
        }

        return redirect()->to('/auth/login');
    }
}
