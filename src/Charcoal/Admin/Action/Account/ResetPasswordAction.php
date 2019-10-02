<?php

namespace Charcoal\Admin\Action\Account;

use Exception;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\User;
use Charcoal\Admin\User\LostPasswordToken;

/**
 * Reset Password Action
 *
 * This action is used to process a user's new password given a valid
 * _password reset token_ generared by
 * {@see \Charcoal\Admin\Action\Account\LostPasswordAction}.
 *
 * ## Required Parameters
 *
 * - `token`
 * - `email`
 * - `password1`
 * - `password2`
 * - `g-recaptcha-response`
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Password has been changed
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Password could not be changed
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
     * @todo   This should be done via an Authenticator object.
     * @todo   Implement "sendResetPasswordEmail"
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $translator = $this->translator();

        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

        $token     = $request->getParam('token');
        $email     = $request->getParam('email');
        $password1 = $request->getParam('password1');
        $password2 = $request->getParam('password2');

        if (!$token) {
            $this->addFeedback('error', $translator->translate('Missing reset token.'));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        if (!$email) {
            $this->addFeedback('error', $translator->translate('Missing email.'));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        if (!$password1) {
            $this->addFeedback('error', $translator->translate('Missing password'));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        if (!$password2) {
            $this->addFeedback('error', $translator->translate('Missing password confirmation'));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        if ($password1 != $password2) {
            $this->addFeedback('error', $translator->translate('Passwords do not match'));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        if ($this->recaptchaEnabled() && $this->validateCaptchaFromRequest($request, $response) === false) {
            if ($ip) {
                $logMessage = sprintf(
                    '[Admin] Reset Password — CAPTCHA challenge failed for "%s" from %s',
                    $email,
                    $ip
                );
            } else {
                $logMessage = sprintf(
                    '[Admin] Reset Password — CAPTCHA challenge failed for "%s"',
                    $email
                );
            }

            $this->logger->warning($logMessage);

            return $response;
        }

        $failMessage = $translator->translation('An error occurred while processing the password change.');

        $authenticator = $this->authenticator();

        $user = $authenticator->createUser();
        $user->loadFrom('email', $email);

        if (!$authenticator->validateAuthentication($user)) {
            /**
             * Fail silently — Never confirm or deny the existence
             * of an account with a given email or email.
             */
            if ($ip) {
                $logMessage = sprintf(
                    '[Admin] Reset Password — Can not find "%s" user in database for %s.',
                    $email,
                    $ip
                );
            } else {
                $logMessage = sprintf(
                    '[Admin] Reset Password — Can not find "%s" user in database.',
                    $email
                );
            }
            $this->logger->error($logMessage);

            $this->addFeedback('error', $failMessage);
            $this->setSuccess(false);

            return $response->withStatus(500);
        }

        if (!$this->validateToken($token, $user->id())) {
            $this->setFailureUrl($this->adminUrl('account/lost-password?notice=invalidtoken'));
            $this->addFeedback('error', $translator->translate('Your password reset token is invalid or expired.'));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        try {
            $authenticator->changeUserPassword($user, $password1);
            $this->deleteToken($token);

            $this->addFeedback('success', $translator->translate('Your password has been successfully changed.'));
            $this->setSuccessUrl((string)$this->adminUrl('login?notice=newpass'));
            $this->setSuccess(true);

            return $response;
        } catch (Exception $e) {
            if ($ip) {
                $logMessage = sprintf(
                    '[Admin] Reset Password — Failed to process change for "%s" from %s: %s',
                    $email,
                    $ip,
                    $e->getMessage()
                );
            } else {
                $logMessage = sprintf(
                    '[Admin] Reset Password — Failed to process change for "%s": %s',
                    $email,
                    $e->getMessage()
                );
            }
            $this->logger->error($logMessage);

            $this->addFeedback('error', $failMessage);
            $this->setSuccess(false);

            return $response->withStatus(500);
        }

        return $response;
    }

    /**
     * @return array
     */
    public function results()
    {
        $ret = [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks(),
        ];

        return $ret;
    }

    /**
     * Validate the given password reset token.
     *
     * To be valid, a token should:
     *
     * - exist in the database
     * - not be expired
     * - match the given user
     *
     * @see    \Charcoal\Admin\Template\Account::validateToken()
     * @param  string $token  The token to validate.
     * @param  string $userId The user ID that should match the token.
     * @return boolean
     */
    private function validateToken($token, $userId)
    {
        $obj = $this->modelFactory()->create(LostPasswordToken::class);
        $sql = strtr('SELECT * FROM `%table` WHERE `token` = :token AND `user` = :userId AND `expiry` > NOW()', [
            '%table' => $obj->source()->table(),
        ]);
        $obj->loadFromQuery($sql, [
            'token'  => $token,
            'userId' => $userId,
        ]);

        return !!$obj->token();
    }

    /**
     * Delete the given password reset token.
     *
     * @param  string $token The token to delete.
     * @return void
     */
    private function deleteToken($token)
    {
        $obj = $this->modelFactory()->create(LostPasswordToken::class);
        $obj->setToken($token);
        $obj->delete();
    }
}
