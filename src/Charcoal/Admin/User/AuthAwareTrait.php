<?php

namespace Charcoal\Admin\User;

use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;
use Pimple\Container;

trait AuthAwareTrait
{
    /**
     * @var Authenticator $authenticator
     */
    private $authenticator;

    /**
     * @var Authorizer $authorizer
     */
    private $authorizer;

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function setAuthDependencies(Container $container)
    {
        $this->setAuthenticator($container['admin/authenticator']);
        $this->setAuthorizer($container['admin/authorizer']);
    }

    /**
     * @param Authenticator $authenticator The authentication service.
     * @return self
     */
    protected function setAuthenticator(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * @return Authenticator
     * @throws \Exception When setAuthDependencies is not called from class.
     */
    protected function authenticator()
    {
        if (!$this->authenticator) {
            throw new \Exception(
                sprintf(
                    'AuthAwareTrait::setAuthDependencies must be set in %s',
                    self::class
                )
            );
        }

        return $this->authenticator;
    }

    /**
     * @param Authorizer $authorizer The authorization service.
     * @return self
     */
    protected function setAuthorizer(Authorizer $authorizer)
    {
        $this->authorizer = $authorizer;

        return $this;
    }

    /**
     * @return Authorizer
     */
    protected function authorizer()
    {
        return $this->authorizer;
    }
}
