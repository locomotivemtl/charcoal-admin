<?php

namespace Charcoal\Admin\Action\Account;

use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Pimple\Container;

// Modele `charcoal-factory
use \Charcoal\Factory\FactoryInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;
use \Charcoal\Admin\User;
use \Charcoal\Admin\User\LostPasswordToken;

/**
 * Reset password action
 *
 * ## Required parameters
 *
 * - `username`
 * - `g-recaptcha-response`
 */
class ResetPasswordAction extends AdminAction
{
    /**
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }

    /**
     * Note that the lost-password action should never change status code and always return 200.
     *
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     * @todo This should be done via an Authenticator object.
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $token = $request->getParam('token');
        $username = $request->getParam('username');
        $password = $request->getParam('password');
        $passwordConfirm = $request->getParam('password_confirm');

        if (!$token) {
            $this->addFeedback('error', 'Missing token.');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        if (!$username) {
            $this->addFeedback('error', 'Missing username.');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        if (!$password) {
            $this->addFeedback('error', 'Missing password');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        if ($password != $passwordConfirm) {
            $this->addFeedback('error', 'Passwords do not match');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        $recaptchaValue = $request->getParam('g-recaptcha-response');
        if (!$recaptchaValue) {
            $this->addFeedback('error', 'Missing captcha.');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }
        if (!$this->validateCaptcha($recaptchaValue)) {
            $this->addFeedback('error', 'Invalid captcha.');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        $user = $this->loadUser($username);
        if (!$user) {
            $this->addFeedback('error', 'Invalid user.');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        if (!$this->validateToken($token, $user->id())) {
            $this->addFeedback('error', 'Invalid or expired token.');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        try {
            $user->resetPassword($password);
            $this->addFeedback('success', 'Invalid or expired token.');
            $this->setSuccess(true);
            $this->deleteToken($token);
            return $response;
        } catch (Exception $e) {
            $this->logger->error('Error resetting password: '.$e->getMessage());
            $this->addFeedback('error', 'Error resetting password.');
            return $response->withStatus(404);
        }


        return $response;
    }

    /**
     * @param string $response The captcha value (response) to validate.
     * @return boolean
     */
    private function validateCaptcha($response)
    {
        $validationUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $secret = $this->appConfig['apis.google.recaptcha.secret'];
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $response = file_get_contents($validationUrl.'?secret='.$secret.'&response='.$response.'&remoteip='.$ip);
        $response = json_decode($response, true);

        return !!$response['success'];
    }

    /**
     * @return array
     */
    public function results()
    {
        $ret = [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];

        return $ret;
    }

    /**
     * @param string $username Username or email.
     * @return User|false
     */
    private function loadUser($username)
    {
        if (!$username) {
            return false;
        }
        // Try to get user by username
        $user = $this->modelFactory()->create(User::class);
        $user->loadFrom('username', $username);
        if ($user->id()) {
            return $user;
        }
        // Try to get user by email
        $user->loadFrom('email', $username);
        if ($user->id()) {
            return $user;
        }
        return false;
    }

    /**
     * To be valid, a token should:
     *
     * - exist in the database
     * - not be expired
     * - match the given user
     *
     * @param string $token    The token to validate.
     * @param string $username The user that should match the token.
     * @return boolean
     */
    private function validateToken($token, $username)
    {
        $tokenProto = $this->modelFactory()->create(LostPasswordToken::class);
        $q = '
        select
            *
        from
            `'.$tokenProto->source()->table().'`
        where
            `token`=:token
        and
            `user`=:username
        and
            `expiry` > NOW()';
        $tokenProto->loadFromQuery($q, [
            'token' => $token,
            'username'  => $username
        ]);
        return !!$tokenProto->token();
    }

    /**
     * @param string $token The token to delete.
     * @return void
     */
    private function deleteToken($token)
    {
        $tokenProto = $this->modelFactory()->create(LostPasswordToken::class);
        $tokenProto->setToken($token);
        $tokenProto->delete();
    }
}
