<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ThrottleFilter
 *
 * Rate-limits login attempts per IP address.
 * Applied only to POST /auth/authenticate via Routes.php.
 *
 * Limits: 5 attempts per IP per 15-minute window.
 * Storage: CI4 file cache (WRITEPATH/cache) — no Redis/memcached needed.
 */

class ThrottleFilter implements FilterInterface
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
     * @return mixed
     */

    private const MAX_ATTEMPTS  = 5;
    private const DECAY_SECONDS = 15 * 60; // 15 minutes

    public function before(RequestInterface $request, $arguments = null)
    {
        $cache = \Config\Services::cache();
        $ip    = $request->getIPAddress();
        $key   = 'login_attempts_' . md5($ip);

        $attempts = (int) ($cache->get($key) ?? 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            // Return a redirect — same as a wrong-password response so the
            // caller can't distinguish "locked out" from "wrong credentials".
            return redirect()
                ->to('/auth/login')
                ->withInput()
                ->with(
                    'error',
                    'Too many sign-in attempts. Please wait 15 minutes and try again.'
                );
        }

        // Increment. On first attempt, also set the TTL.
        $cache->save($key, $attempts + 1, self::DECAY_SECONDS);
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
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
