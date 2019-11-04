<?php

namespace Charcoal\Tests\Admin\Mock;

// From 'charcoal-admin'
use Charcoal\Admin\User\AuthToken as AdminAuthtoken;

/**
 * Mock AuthToken
 *
 * This class was created to mock the `setcookie()` function
 * used by {@see \Charcoal\User\AuthTokenCookieTrait}.
 */
class AuthToken extends AdminAuthtoken
{
    /**
     * @return boolean
     */
    public function sendCookie()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function deleteCookie()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return true;
    }
}
