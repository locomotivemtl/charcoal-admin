<?php

namespace Charcoal\Admin\Support;

/**
 * Security Support Trait
 */
trait SecurityTrait
{
    /**
     * Determine if user authentication is required.
     *
     * Authentication is required by default. If unnecessary,
     * replace this method in the inherited template class.
     *
     * For example, the "Login" / "Reset Password" templates
     * should return `false`.
     *
     * @return boolean
     */
    protected function authRequired()
    {
        return true;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticator()->check();
    }

    /**
     * Retrieve the currently authenticated user.
     *
     * @return \Charcoal\User\UserInterface|null
     */
    public function getAuthenticatedUser()
    {
        return $this->authenticator()->user();
    }

    /**
     * Retrieve the authentication service.
     *
     * @return \Charcoal\User\Authenticator
     */
    abstract protected function authenticator();
}
