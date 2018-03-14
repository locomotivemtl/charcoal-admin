<?php

namespace Charcoal\Admin\Action;

// From 'charcoal-admin'
use Charcoal\Admin\User;

/**
 *
 */
trait AuthActionTrait
{
    /**
     * @return \Charcoal\Factory\FactoryInterface The model factory.
     */
    abstract protected function modelFactory();

    /**
     * Retrieve the user with the given login handle.
     *
     * @param  string $handle A login name or email address.
     * @return User|null Returns the user object instance or NULL.
     */
    protected function loadUser($handle)
    {
        if (!$handle) {
            return null;
        }

        // Try to get user by username
        $user = $this->modelFactory()->create(User::class);
        $user->loadFrom('username', $handle);
        if ($user->id()) {
            return $user;
        }

        // Try to get user by email
        $user->loadFrom('email', $handle);
        if ($user->id()) {
            return $user;
        }

        return null;
    }
}
