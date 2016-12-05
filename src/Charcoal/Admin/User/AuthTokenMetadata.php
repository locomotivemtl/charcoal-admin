<?php

namespace Charcoal\Admin\User;

use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Model\ModelMetadata;

/**
 * Admin Auth Token metadata
 */
class AuthTokenMetadata extends ModelMetadata
{
    /**
     * @var boolean $enabled
     */
    private $enabled;

    /**
     * @var string $cookieName
     */
    private $cookieName;

    /**
     * @var string $cookieDuration
     */
    private $cookieDuration;

    /**
     * @var bool $httpsOnly
     */
    private $httpsOnly;

    /**
     * @return array
     */
    public function defaults()
    {
        $parentDefaults = parent::defaults();

        $defaults = array_replace_recursive($parentDefaults, [
            'enabled'         => true,
            'cookie_name'     => 'charcoal_admin_login',
            'cookie_duration' => '15 days',
            'https_only'      => false
        ]);
        return $defaults;
    }

    /**
     * @param boolean $enabled The enabled flag.
     * @return AuthTokenMetadata Chainable
     */
    public function setEnabled($enabled)
    {
        $this->enabled = !!$enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function enabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $name The cookie name.
     * @throws InvalidArgumentException If the cookie name is not a string.
     * @return AuthTokenMetadata Chainable
     */
    public function setCookieName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                'Can not set auth token\'s cookie  name: must be a string'
            );
        }
        $this->cookieName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function cookieName()
    {
        return $this->cookieName;
    }

    /**
     * @param string $duration The cookie duration, or duration. Ex: "15 days".
     * @throws InvalidArgumentException If the cookie name is not a string.
     * @return AuthTokenMetadata Chainable
     */
    public function setCookieDuration($duration)
    {
        if (!is_string($duration)) {
            throw new InvalidArgumentException(
                'Can not set auth token\'s cookie duration: must be a string'
            );
        }
        $this->cookieDuration = $duration;
        return $this;
    }

    /**
     * @return string
     */
    public function cookieDuration()
    {
        return $this->cookieDuration;
    }

    /**
     * @param boolean $httpsOnly The "https only" flag.
     * @return AuthTokenMetadata Chainable
     */
    public function setHttpsOnly($httpsOnly)
    {
        $this->httpsOnly = !!$httpsOnly;
        return $this;
    }

    /**
     * @return boolean
     */
    public function httpsOnly()
    {
        return $this->httpsOnly;
    }
}
