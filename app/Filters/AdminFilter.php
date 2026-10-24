<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AdminFilter
 *
 * Guards all routes in the 'admin' group.
 * Requires the user to be logged in AND have role = 'admin'.
 * Returns 403 JSON for AJAX requests so admin-only table endpoints
 * don't silently break when a non-admin somehow reaches them.
 */

class AdminFilter implements FilterInterface
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
    public function before(RequestInterface $request, $arguments = null)
    {
        // Must be logged in first.
        if (! session()->get('is_logged_in')) {
            return $this->deny($request, 401, 'Unauthenticated.', '/auth/login');
        }

        // Must be an admin.
        if (session()->get('role') !== 'admin') {
            return $this->deny($request, 403, 'Access denied. Admin privileges required.', '/');
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

    private function deny(
        RequestInterface $request,
        int $statusCode,
        string $message,
        string $redirectTo
    ): ResponseInterface {
        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $isAjax = $request->isAJAX()
            || str_contains((string) $request->getHeaderLine('Accept'), 'application/json');

        if ($isAjax) {
            return response()
                ->setJSON(['status' => 'error', 'message' => $message])
                ->setStatusCode($statusCode);
        }

        if ($statusCode === 403) {
            session()->setFlashdata('error', $message);
        }

        return redirect()->to($redirectTo);
    }
}
