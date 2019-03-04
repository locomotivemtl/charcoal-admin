<?php

namespace Charcoal\Tests\Admin\Mock;

// From 'charcoal-user'
use Charcoal\User\UserInterface;

// From 'charcoal-admin'
use Charcoal\Admin\User;
use Charcoal\Tests\AbstractTestCase;

/**
 * User Testing Helpers
 */
trait UserProviderTrait
{
    /**
     * User model class name.
     *
     * Must be a fully-qualified PHP namespace and an implementation of
     * {@see \Charcoal\User\UserInterface}. Used by the model factory.
     *
     * @var string
     */
    protected $userClass = User::class;

    /**
     * Create a user model and save it into storage.
     *
     * @param  string $email    The user's email address.
     * @param  string $password The user's password.
     * @return UserInterface
     */
    protected function createUser(
        $email,
        $password = 'qwerty'
    ) {
        $container = $this->container();

        $user = $container['model/factory']->create($this->userClass);
        $user->setData([
            'email'    => $email,
            'password' => $password,
        ]);

        $user->save();
        $user->loadFrom('email', $email);

        return $user;
    }

    /**
     * Determine if the user exists.
     *
     * @param  string $email The email to lookup.
     * @return User
     */
    protected function userExists($email)
    {
        $container = $this->container();

        $user = $container['model/factory']->create($this->userClass);
        $user->loadFrom('email', $email);

        return !!$user->id();
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    abstract protected function container();
}
