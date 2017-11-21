<?php

namespace Charcoal\Admin\Script\User;

use Exception;

// PSR-7 (http messaging) dependencies
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminScript;
use Charcoal\Admin\User;

/**
 * Create admin user script.
 */
class CreateScript extends AdminScript
{
    const MIN_PASSWORD_LENGTH = 5;

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
                'prefix'      => 'u',
                'longPrefix'  => 'username',
                'description' => 'The user name'
            ],
            'email' => [
                'prefix'      => 'e',
                'longPrefix'  => 'email',
                'description' => 'The user email'
            ],
            'password' => [
                'prefix'      => 'p',
                'longPrefix'  => 'password',
                'description' => 'The user password',
                'inputType'   => 'password'
            ],
            'roles' => [
                'prefix'      => 'r',
                'longPrefix'  => 'roles',
                'description' => 'The user role'
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

        try {
            $this->createUser();
        } catch (Exception $e) {
            $this->climate()->red()->out("\nError. User could not be created");
            $this->climate()->red()->out($e->getMessage());
        }

        return $response;
    }

    /**
     * Create a new user in the database
     * @return void
     */
    private function createUser()
    {
        $this->climate()->underline()->out(
            $this->translator()->translate('Create a new Charcoal Administrator User')
        );

        $user       = $this->modelFactory()->create(User::class);
        $prompts = $this->userPrompts();
        $properties = $user->properties(array_keys($prompts));

        $vals = [];
        foreach ($properties as $prop) {
            if (!in_array($prop->ident(), array_keys($prompts))) {
                continue;
            }

            $prompt = $prompts[$prop->ident()];

            if ($prompt['property']) {
                $v = $prompt['property'];
            } else {
                $v = $this->promptProperty($prop, $prompt['label']);
            }
            if (isset($prompt['validation'])) {
                call_user_func($prompt['validation'], $v);
            }

            $prop->setVal($v);
            $vals[$prop->ident()] = $v;
        }

        // Trigger reset password
        $user->resetPassword($vals['password']);
        unset($vals['password']);

        $user->setFlatData($vals);
        $ret = $user->save();

        if ($ret) {
            $this->climate()->green()->out("\n".sprintf('Success! User "%s" created.', $ret));
        } else {
            $this->climate()->red()->out("\nError. User could not be created.");
        }
    }

    /**
     * @return array
     */
    private function userPrompts()
    {
        $translator = $this->translator();

        return [
            'username' => [
                'label'      => $translator->translate('Please enter username: '),
                'property'   => $this->climate()->arguments->get('username'),
                'validation' => [$this, 'validateUsername']
            ],
            'email' => [
                'label'      => $translator->translate('Please enter email: '),
                'property'   => $this->climate()->arguments->get('email'),
                'validation' => [$this, 'validateEmail']
            ],
            'password' => [
                'label'      => $translator->translate('Please enter password: '),
                'property'   => $this->climate()->arguments->get('password'),
                'validation' => [$this, 'validatePassword']
            ],
            'roles' => [
                'label'      => $translator->translate('Please enter role(s) [ex: admin], comma separated: '),
                'property'   => $this->climate()->arguments->get('roles'),
                'validation' => null
            ]
        ];
    }

    /**
     * @param mixed  $prop  The property.
     * @param string $label The label.
     * @return mixed
     */
    private function promptProperty($prop, $label)
    {
        $input = $this->propertyToInput($prop, $label);
        $v     = $input->prompt();
        if ($prop->type() == 'password') {
            $this->climate()->dim()->out('');
        }

        return $v;
    }

    /**
     * @param string $username The username, from input.
     * @throws Exception If the username is empty or already exists in the database.
     * @return void
     */
    private function validateUsername($username)
    {
        if (!$username) {
            throw new Exception(
                $this->translator()->translate('Username can not be empty.')
            );
        }
        $user       = $this->modelFactory()->create(User::class);
        $user->load($username);
        if ($user->username()) {
            throw new Exception(sprintf(
                $this->translator()->translate('Username "%s" already exists in database.'),
                $username
            ));
        }
    }

    /**
     * @param string $email The email, from input.
     * @throws Exception If the email is empty or invalid (validated with php's filters).
     * @return void
     */
    private function validateEmail($email)
    {
        if (!$email) {
            throw new Exception(
                $this->translator()->translate('Email can not be empty.')
            );
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(sprintf(
                $this->translator()->translate('Invalid email "%s".'),
                $email
            ));
        }
    }

    /**
     * @param string $password The password, from input.
     * @throws Exception If the password is empty or too small.
     * @return void
     */
    private function validatePassword($password)
    {
        if (!$password) {
            throw new Exception(
                $this->translator()->translate('Password can not be empty.')
            );
        }
        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new Exception(
                $this->translator()->translate(sprintf(
                    'Password must be at least %d characters.',
                    self::MIN_PASSWORD_LENGTH
                ))
            );
        }
    }
}
