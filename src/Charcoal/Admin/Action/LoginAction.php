<?php

namespace Charcoal\Admin\Action;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Charcoal\Admin\AdminAction;
use \Charcoal\Admin\User;

/**
 * Admin Login Action: Attempt to log a user in.
 *
 * ## Parameters
 * **Required parameters**
 * - `username`
 * - `password`
 * **Optional parameters**
 * - `nextUrl`
 *
 * ## Response
 * - `success` true if login was successful, false otherwise.
 *   - Failure should also send a different HTTP code: see below.
 * - `feedbacks` (Optional) operation feedbacks, if any.
 * - `nextUrl` Redirect URL, in case of successfull login.
 *   - This is the `nextUrl` parameter if it was set, or the default admin URL if not
 *
 * ## HTTP Codes
 * - `200` in case of a successful login
 * - `403` in case of wrong credentials
 * - `404` if a required parameter is missing
 *
 */
class LoginAction extends AdminAction
{
    /**
     * @var string $nextUrl
     */
    protected $nextUrl;

    /**
     * Authentication is required by default.
     *
     * Change to false in
     *
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }


    /**
     * Assign the next URL.
     *
     * Note that any string is accepted. It should be validated before using this method.
     *
     * @param string $nextUrl
     * @throws InvalidArgumentException If the $nextUrl parameter is not a string.
     */
    public function setNextUrl($nextUrl)
    {
        if (!is_string($nextUrl)) {
            throw new InvalidArgumentException(
                'Next URL needs to be a string'
            );
        }
        $this->nextUrl = $nextUrl;
        return $this;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        if (!$username || !$password) {
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        $this->logger()->debug(
            sprintf('Admin login attempt: "%s"', $username)
        );

        $u = new User([
            'logger' => $this->logger()
        ]);

        try {
            $isAuthenticated = $u->authenticate($username, $password);
        } catch (Exception $e) {
            $isAuthenticated = false;
        }

        if (!$isAuthenticated) {
            $this->logger()->warning(
                sprintf('Login attempt failure: "%s"', $username)
            );
            $this->setSuccess(false);
            return $response->withStatus(403);
        } else {
            $this->logger()->debug(
                sprintf('Login attempt successful: "%s"', $username)
            );
            $this->setSuccess(true);
            return $response;
        }
    }

    /**
     * @return array
     */
    public function results()
    {
        $results = [
            'success'   => $this->success(),
            'nextUrl'  => 'home'
        ];
        return $results;
    }
}
