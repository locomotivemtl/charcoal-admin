<?php

namespace Charcoal\Admin\Script\User;

use Exception;

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
 * Create admin user script.
 */
class CreateScript extends AdminScript implements
    AuthAwareInterface
{
    use AuthAwareTrait;

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
            ],
            'display_name' => [
                'prefix'      => 'n',
                'longPrefix'  => 'name',
                'description' => 'The user display name'
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
        $authenticator = $this->authenticator();

        $climate = $this->climate();

        $climate->underline()->out(
            $this->translator()->translate('Create a new Charcoal Administrator User')
        );

        $user       = $authenticator->createUser();
        $prompts    = $this->userPrompts();
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
        $authenticator->changeUserPassword($user, $vals['password'], false);
        unset($vals['password']);

        $user->setFlatData($vals);
        $result = $user->save();

        if ($result) {
            $climate->green()->out("\n".sprintf('Success! User "%s" created.', $user['email']));
        } else {
            $climate->red()->out("\nError. User could not be created.");
        }
    }

    /**
     * @return array
     */
    private function userPrompts()
    {
        $translator = $this->translator();
        $climate    = $this->climate();

        return [
            'email' => [
                'label'      => $translator->translate('Please enter email: '),
                'property'   => $climate->arguments->get('email'),
                'validation' => [ $this, 'validateEmail' ],
            ],
            'password' => [
                'label'      => $translator->translate('Please enter password: '),
                'property'   => $climate->arguments->get('password'),
                'validation' => [ $this, 'validatePassword' ],
            ],
            'roles' => [
                'label'      => $translator->translate('Please enter role(s) [ex: admin], comma separated: '),
                'property'   => $climate->arguments->get('roles'),
                'validation' => null,
            ],
            'display_name' => [
                'label'      => $translator->translate('Please enter a name: '),
                'property'   => $climate->arguments->get('display_name'),
                'validation' => null,
            ],
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
        $value = $input->prompt();
        if ($prop->type() === 'password') {
            $this->climate()->dim()->out('');
        }

        return $value;
    }

    /**
     * @param string $email The email, from input.
     * @throws Exception If the email is empty or invalid (validated with php's filters)
     *         or already exists in the database.
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
        $user = $this->modelFactory()->create(User::class);
        $user->loadFrom('email', $email);
        if ($user['id'] !== null) {
            throw new Exception(sprintf(
                $this->translator()->translate('Email "%s" already exists in database.'),
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
