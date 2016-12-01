<?php

namespace Charcoal\Admin\Action\Account;

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
 * Lost password action
 *
 * ## Required parameters
 *
 * - `username`
 */
class LostPasswordAction extends AdminAction
{
    /**
     * @var FactoryInterface
     */
    private $emailFactory;

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->setEmailFactory($container['email/factory']);
    }

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
        $username = $request->getParam('username');

        if (!$username) {
            $this->addFeedback('error', 'Missing username.');
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
            // Fail silently.
            $this->logger->error('Lost password request: can not find user in database.');
            return $response;
        }

        $token = $this->generateLostPasswordToken($user);
        $this->sendLostPasswordEmail($user, $token);

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
     *
     * @param User   $user  The user to send the lost-password email to.
     * @param string $token The lost-password token, as string.
     * @return void
     */
    private function sendLostPasswordEmail(User $user, $token)
    {
        $userEmail = $user->email();

        $subject = 'Charcoal lost password';
        $from = 'charcoal@locomotive.ca';

        // Create email
        $emailObj = $this->emailFactory->create('email');
        $emailObj->setData([
            'campaign'          => 'admin.lost-password',
            'to'                => $userEmail,
            'subject'           => $subject,
            'from'              => $from,
            'log'               => true,
            'template_ident'    => 'charcoal/admin/email/user.lost-password',
            'template_data'     => [
                'user'  => $user,
                'token' => $token->id(),
                'urlResetPassword' => $this->adminUrl().'account/reset-password/'.$token->id(),
                'expiry'=> $token->expiry()->format('Y-m-d H:i:s'),
                'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
            ]
        ]);
        $emailObj->send();
    }

    /**
     * @param FactoryInterface $factory The email factory, to create email objects.
     * @return void
     */
    private function setEmailFactory(FactoryInterface $factory)
    {
        $this->emailFactory = $factory;
    }
}
