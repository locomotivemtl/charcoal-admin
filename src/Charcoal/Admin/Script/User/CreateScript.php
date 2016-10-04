<?php

namespace Charcoal\Admin\Script\User;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminScript;
use Psr\Log\NullLogger;

/**
 * Create user script.
 */
class CreateScript extends AdminScript
{

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
            'databaseName'     => [
                'prefix'      => 'u',
                'longPrefix'  => 'username',
                'description' => 'The user name'
            ],
            'databaseUser'     => [
                'prefix'      => 'e',
                'longPrefix'  => 'email',
                'description' => 'The user email'
            ],
            'databasePassword' => [
                'prefix'      => 'p',
                'longPrefix'  => 'password',
                'description' => 'The user password'
            ],
            'databaseHost'     => [
                'prefix'      => 'r',
                'longPrefix'  => 'role',
                'description' => 'The user role'
            ]
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);

        return $arguments;
    }

    /**
     * CreateScript constructor Register the action's arguments..
     */
    public function __construct()
    {
        $arguments = $this->defaultArguments();
        $this->setArguments($arguments);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $this->createUser();

        return $response;
    }

    /**
     * Create a new user in the database
     * @return void
     */
    private function createUser()
    {
        $climate = $this->climate();

        $climate->underline()->out(
            'Create a new Charcoal Administrator'
        );

        $user       = $this->modelFactory()->create('charcoal/admin/user');
        $properties = $user->properties();

        $shown_props = [
            'username',
            'email',
            'roles',
            'password'
        ];

        $vals = [];
        foreach ($properties as $prop) {
            if (!in_array($prop->ident(), $shown_props)) {
                continue;
            }
            if ($prop->type() == 'password') {
                $input = $climate->password(sprintf('Enter value for "%s":', $prop->label()));
            } else {
                $input = $climate->input(sprintf('Enter value for "%s":', $prop->label()));
            }
            $input = $this->propertyToInput($prop);
            $v     = $input->prompt();

            $prop->setVal($v);
            $valid                = $prop->validate();
            $vals[$prop->ident()] = $v;
        }

        $user->resetPassword($vals['password']);
        unset($vals['password']);

        $user->setFlatData($vals);

        $ret = $user->save();
        if ($ret) {
            $climate->green()->out("\n".sprintf('Success! User "%s" created.', $ret));
        } else {
            $climate->red()->out("\nError. Object could not be created.");
        }
    }

    /**
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }
}
