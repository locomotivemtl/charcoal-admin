<?php

namespace Charcoal\Admin\Script\User;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\AuthAwareInterface;
use Charcoal\User\AuthAwareTrait;

// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;
use Charcoal\Admin\User;

/**
 *
 */
class ResetPasswordScript extends AdminScript implements
    AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @param array|\ArrayAccess $data The dependencies (app and logger).
     */
    public function __construct($data = null)
    {
        parent::__construct($data);

        $arguments = $this->defaultArguments();
        $this->setArguments($arguments);
    }

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies AuthAwareInterface
        $this->setAuthenticator($container['admin/authenticator']);
    }

    /**
     * Retrieve the available default arguments of this action.
     *
     * @link http://climate.thephpleague.com/arguments/ For descriptions of the options for CLImate.
     *
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'email' => [
                'prefix'        => 'e',
                'longPrefix'    => 'email',
                'description'   => 'The user email'
            ],
            'password' => [
                'prefix'        => 'p',
                'longPrefix'    => 'password',
                'description'   => 'The user password',
                'inputType'     => 'password'
            ],
            'sendEmail' => [
                'longPrefix'    => 'send-email',
                'description'   => 'If set, an email will be sent to the user.',
                'noValue'       => true,
                'defaultValue'  => false
            ]
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);

        return $arguments;
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $authenticator = $this->authenticator();

        $climate = $this->climate();

        $climate->underline()->out(
            'Reset a Charcoal Administrator password'
        );

        $user = $authenticator->createUser();

        $email = $this->argOrInput('email');
        $user->loadFrom('email', $email);

        if (!$authenticator->validateAuthentication($user)) {
            $climate->red()->out(
                'User does not exist.'
            );
            return $response;
        }

        $passKey  = $user->getAuthPasswordKey();
        $password = $this->argOrInput($passKey);

        $authenticator->changeUserPassword($user, $password);

        if ($climate->arguments->get('sendEmail')) {
            $this->sendResetPasswordEmail($email, $password);
        }

        $climate->red()->out(
            sprintf('User "%s" password has been modified.', $email)
        );

        return $response;
    }

    /**
     * @param  string $email    The user email.
     * @param  string $password The new, plain-text password.
     * @return void
     * @todo   Implement reset password email dispatch.
     */
    private function sendResetPasswordEmail($email, $password)
    {
    }
}
