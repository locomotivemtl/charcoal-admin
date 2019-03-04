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
     * @param  string $handle An ID or email address.
     * @return User|null Returns the user object instance or NULL.
     */
    protected function loadUser($handle)
    {
        if (!$handle) {
            return null;
        }

        // Try to get user by ID
        $user = $this->modelFactory()->create(User::class);
        $user->load($handle);
        if ($user->id() !== null) {
            return $user;
        }

        // Try to get user by email
        $user->loadFrom('email', $handle);
        if ($user->id() !== null) {
            return $user;
        }

        return null;
    }
}
