<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected Session $session;

    /**
     * @var array{
     *     id: int,
     *     name: string,
     *     role: string
     * }
     */
    protected array $currentUser = [
        'id'   => 0,
        'name' => '',
        'role' => 'guest',
    ];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = service('session');

        $this->currentUser = [
            'id'   => (int) ($this->session->get('user_id') ?? 0),
            'name' => (string) ($this->session->get('name') ?? ''),
            'role' => (string) ($this->session->get('role') ?? 'guest'),
        ];
    }

    /**
     * Get common data shared by application views.
     *
     * @return array{
     *     currentUser: array{
     *         id: int,
     *         name: string,
     *         role: string
     *     }
     * }
     */
    protected function viewData(array $data = []): array
    {
        return array_merge(
            [
                'currentUser' => $this->currentUser,
            ],
            $data
        );
    }
}
