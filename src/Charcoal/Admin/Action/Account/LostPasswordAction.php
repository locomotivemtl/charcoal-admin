<?php

namespace Charcoal\Admin\Action\Account;

use Exception;
use RuntimeException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\User;
use Charcoal\Admin\User\LostPasswordToken;

/**
 * Lost Password Action
 *
 * This action is used to generate a time-sensitive _password reset token_
 * for which a link will be sent via email to the address assigned to the
 * submitted username. Consult {@see \Charcoal\Admin\Action\Account\ResetPasswordAction}
 * for processing a reset token.
 *
 * ## Required Parameters
 *
 * - `username`
 * - `g-recaptcha-response`
 *
 * ## HTTP Status Codes
 *
 * - `200` — Either a reset token is generated and emailed to user
 *   or the action failed silently (nonexistent user account)
 * - `400` — Client error; Invalid request data
 * - `500` — Server error
 */
class LostPasswordAction extends AdminAction
{
    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $emailFactory;

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
        $translator = $this->translator();
        $ip   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

        $username = $request->getParam('username');
        if (!$username) {
            $this->addFeedback('error', $translator->translate('Missing username.'));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        if ($this->recaptchaEnabled() && $this->validateCaptchaFromRequest($request, $response) === false) {
            if ($ip) {
                $logMessage = sprintf('[Admin] Lost Password Request — CAPTCHA challenge failed for "%s" from %s', $username, $ip);
            } else {
                $logMessage = sprintf('[Admin] Lost Password Request — CAPTCHA challenge failed for "%s"', $username);
            }

            $this->logger->warning($logMessage);

            return $response;
        }

        $doneMessage = $translator->translate('If a registered user matches the username or email address given, instructions to reset your password have been sent to the email address registered with that account.');
        $failMessage = $translator->translate('An error occurred while processing the password reset request.');


        $user = $this->loadUser($username);
        if ($user === false) {
            /**
             * Fail silently — Never confirm or deny the existence
             * of an account with a given email or username.
             */
            if ($ip) {
                $logMessage = sprintf(
                    '[Admin] Lost Password Request — Can not find "%s" user in database for %s.',
                    $username,
                    $ip
                );
            } else {
                $logMessage = sprintf(
                    '[Admin] Lost Password Request — Can not find "%s" user in database.',
                    $username
                );
            }
            $this->logger->error($logMessage);

            $this->addFeedback('success', $doneMessage);
            $this->setSuccess(true);

            return $response;
        }

        try {
            $token = $this->generateLostPasswordToken($user);
            $this->sendLostPasswordEmail($user, $token);

            $this->addFeedback('success', $doneMessage);
            $this->setSuccess(true);

            return $response;
        } catch (Exception $e) {
            if ($ip) {
                $logMessage = sprintf(
                    '[Admin] Lost Password Request — Failed to process request for "%s" from %s: %s',
                    $username,
                    $ip,
                    $e->getMessage()
                );
            } else {
                $logMessage = sprintf(
                    '[Admin] Lost Password Request — Failed to process request for "%s": %s',
                    $username,
                    $e->getMessage()
                );
            }
            $this->logger->error($logMessage);

            $this->addFeedback('error', $failMessage);
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
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
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->setEmailFactory($container['email/factory']);
    }

    /**
     * Retrieve the email model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    protected function emailFactory()
    {
        if (!isset($this->emailFactory)) {
            throw new RuntimeException(
                sprintf('Email Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->emailFactory;
    }

    /**
     * Set an email model factory.
     *
     * @param FactoryInterface $factory The email factory, to create emails.
     * @return self
     */
    private function setEmailFactory(FactoryInterface $factory)
    {
        $this->emailFactory = $factory;

        return $this;
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
     * @param User $user The user to genereate a password-token to.
     * @return LostPasswordToken
     */
    private function generateLostPasswordToken(User $user)
    {
        $token = $this->modelFactory()->create(LostPasswordToken::class);
        $token->setData([
            'user' => $user->id()
        ]);
        $token->save();
        return $token;
    }

    /**
     * @todo   Implement `$container['admin/config']['user.lost_password_email']`
     * @param  User   $user  The user to send the lost-password email to.
     * @param  string $token The lost-password token, as string.
     * @return void
     */
    private function sendLostPasswordEmail(User $user, $token)
    {
        $translator = $this->translator();
        $userEmail  = $user->email();
        $siteName   = $this->siteName();

        if ($siteName) {
            $subject = strtr($translator->translate('{{ siteName }} — Password Reset'), [
                '{{ siteName }}' => $siteName
            ]);
        } else {
            $subject = $translator->translate('Charcoal — Password Reset');
        }

        $from = [
            'name'  => 'Charcoal',
            'email' => 'charcoal@locomotive.ca'
        ];

        // Create email
        $emailObj = $this->emailFactory->create('email');
        $emailObj->setData([
            'campaign'          => 'admin.lost-password',
            'to'                => $userEmail,
            'subject'           => (string)$subject,
            'from'              => $from,
            'log'               => true,
            'template_ident'    => 'charcoal/admin/email/user.lost-password',
            'template_data'     => [
                'user'             => $user,
                'token'            => $token->id(),
                'siteName'         => $siteName,
                'adminUrl'         => $this->adminUrl(),
                'urlResetPassword' => $this->adminUrl().'account/reset-password/'.$token->id(),
                'expiry'           => $token->expiry()->format('Y-m-d H:i:s'),
                'ipAddress'        => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
            ]
        ]);
        $emailObj->send();
    }
}
