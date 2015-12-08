<?php

namespace Charcoal\Admin\Action;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\User as User;

/**
* Admin Login Action: Attempt to log a user in.
*
* ## Parameters
* **Required parameters**
* - `username`
* - `password`
* **Optional parameters**
* - `next_url`
*
* ## Response
* - `success` true if login was successful, false otherwise.
*   - Failure should also send a different HTTP code: see below.
* - `feedbacks` (Optional) operation feedbacks, if any.
* - `next_url` Redirect URL, in case of successfull login.
*   - This is the `next_url` parameter if it was set, or the default admin URL if not
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
    * @var string $_next_url
    */
    protected $_next_url;

    /**
    * Authentication is required by default.
    *
    * Change to false in
    *
    * @return boolean
    */
    public function auth_required()
    {
        return false;
    }


    /**
    * Assign the next URL.
    *
    * Note that any string is accepted. It should be validated before using this method.
    *
    * @param string $next_url
    * @throws InvalidArgumentException If the $next_url parameter is not a string.
    */
    public function set_next_url($next_url)
    {
        if (!is_string($next_url)) {
            throw new InvalidArgumentException('Next URL needs to be a string');
        }
        $this->_next_url = $next_url;
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
            $this->set_success(false);
            return $response->withStatus(404);
        }

        $this->logger()->debug(
            sprintf('Admin login attempt: "%s"', $username)
        );

        $u = new User([
            'logger' => $this->logger()
        ]);

        try {
            $is_authenticated = $u->authenticate($username, $password);
        } catch (Exception $e) {
            $is_authenticated = false;
        }

        if (!$is_authenticated) {
            $this->logger()->warning(
                sprintf('Login attempt failure: "%s"', $username)
            );
            $this->set_success(false);
            return $response->withStatus(403);
        } else {
            $this->logger()->debug(
                sprintf('Login attempt successful: "%s"', $username)
            );
            $this->set_success(true);
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
            'next_url'  => $this->redirect_url()
        ];
        return $results;
    }

}
