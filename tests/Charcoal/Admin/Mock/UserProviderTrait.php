<?php

namespace Charcoal\Tests\Admin\Mock;

// From 'charcoal-admin'
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
     * @param  string $username The user's handle and primary key.
     * @param  string $password The user's password.
     * @param  string $email    The user's email address.
     * @return UserInterface
     */
    protected function createUser(
        $username,
        $password = 'qwerty',
        $email = 'foo@example.com'
    ) {
        $container = $this->container();

        $user = $container['model/factory']->create($this->userClass);
        $user->setData([
            'username' => $username,
            'password' => $password,
            'email'    => $email
        ]);

        $user->save();
        $user->load($username);

        return $user;
    }

    /**
     * Determine if the user exists.
     *
     * @param  string $username The username to lookup.
     * @return User
     */
    protected function userExists($username)
    {
        $container = $this->container();

        $user = $container['model/factory']->create($this->userClass);
        $user->load($username);

        return !!$user->id();
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    abstract protected function container();
}
