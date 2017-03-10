<?php

namespace Charcoal\Admin\Action;

use Exception;
use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\User;

/**
 * Action: Attempt to log a user in.
 *
 * ## Required Parameters
 *
 * - `username` (_string_) — The user's handle.
 * - `password` (_string_) — The user's password.
 *
 * ## Optional Parameters
 *
 * - `next_url`
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the user was properly logged out, FALSE in case of any error.
 * - `next_url` (_string_) — Redirect the client on success or failure.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; User has been logged in
 * - `400` — Client error; Invalid or malformed credentials
 * - `401` — Unauthorized
 * - `403` — Forbidden
 * - `500` — Server error; User could not be logged in
 *
 */
class LoginAction extends AdminAction
{
    /**
     * Authentication is required by default.
     *
     * Change to false in the login action controller; this is meant to be called before login.
     *
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $doneMessage = $this->translator()->translation('You have logged in successfully.');
            $failMessage = $this->translator()->translation('An error occurred while logging in');
            $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
                '{{ errorMessage }}' => $failMessage
            ]);

            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

            $username = $request->getParam('username');
            $password = $request->getParam('password');
            $nextUrl  = $request->getParam('next_url');

            if (!$username || !$password) {
                $this->addFeedback('error', $this->translator()->translate('Invalid username or password'));
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            if ($ip) {
                $logMessage = sprintf('[Admin] Login attempt for "%s" from %s', $username, $ip);
            } else {
                $logMessage = sprintf('[Admin] Login attempt for "%s"', $username);
            }
            $this->logger->debug($logMessage);

            $user = $this->authenticator()->authenticateByPassword($username, $password);
            if ($user === null) {
                if ($ip) {
                    $logMessage = sprintf('[Admin] Login failed for "%s" from %s', $username, $ip);
                } else {
                    $logMessage = sprintf('[Admin] Login failed for "%s"', $username);
                }
                $this->logger->warning($logMessage);

                $this->addFeedback('error', $failMessage);
                $this->setSuccess(false);

                return $response->withStatus(403);
            } else {
                $this->setRememberCookie($request, $user);

                if ($ip) {
                    $logMessage = sprintf('[Admin] Login successful for "%s" from %s', $username, $ip);
                } else {
                    $logMessage = sprintf('[Admin] Login successful for "%s"', $username);
                }
                $this->logger->debug($logMessage);

                $this->addFeedback('success', $doneMessage);
                $this->setSuccess(true);

                if (is_string($nextUrl) && !empty($nextUrl)) {
                    $this->setSuccessUrl($nextUrl);
                } else {
                    $this->setSuccessUrl($this->adminUrl());
                }

                return $response;
            }
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }

    /**
     * @param  RequestInterface $request The HTTP request.
     * @param  User             $user    The authenticated user to maybe remember.
     * @return void
     */
    public function setRememberCookie(RequestInterface $request, User $user)
    {
        $remember = $request->getParam('remember-me');
        if (!$remember) {
            return;
        }

        $authToken = $this->modelFactory()->create('charcoal/admin/user/auth-token');
        $authToken->generate($user->username());
        $authToken->sendCookie();

        $authToken->save();
    }
}
