<?php

namespace Charcoal\Admin\Script\User;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminScript;
use \Charcoal\Admin\User;

/**
 *
 */
class ResetPasswordScript extends AdminScript
{
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
     * Retrieve the available default arguments of this action.
     *
     * @link http://climate.thephpleague.com/arguments/ For descriptions of the options for CLImate.
     *
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'username' => [
                'prefix'        => 'u',
                'longPrefix'    => 'username',
                'description'   => 'The user name'
            ],
            'password' => [
                'prefix'        => 'p',
                'longPrefix'    => 'password',
                'description'   => 'The user password',
                'inputType'     => 'password'
            ],
            'sendEmail' => [
                'longPrefix'    => 'send-email',
                'description'   => 'If true, an email will be sent to the user.',
                'noValue'       => true,
                'defaultValue'  => false
            ]
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);

        return $arguments;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $climate = $this->climate();

        $climate->underline()->out(
            'Reset a Charcoal Administrator password'
        );

        $username = $this->argOrInput('username');

        $user = $this->modelFactory()->create(User::class);
        $user->load($username);

        if (!$user->id()) {
            $climate->red()->out(
                'User does not exist.'
            );
            return $response;
        }

        $password = $this->argOrInput('password');

        $user->resetPassword($password);

        if ($climate->arguments->get('sendEmail')) {
            $this->sendResetPasswordEmail($username, $password);
        }

        $climate->red()->out(
            sprintf('User "%s" password has been modified.', $username)
        );

        return $response;
    }

    /**
     * @param  string $username The username.
     * @param  string $password The new, plain-text password.
     * @return void
     * @todo   Implement reset password email dispatch.
     */
    private function sendResetPasswordEmail($username, $password)
    {
    }
}
