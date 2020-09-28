<?php

namespace Charcoal\Admin\User;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 *
 */
class LostPasswordToken extends AbstractModel
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var mixed
     */
    private $user;

    /**
     * @var DateTimeInterface|null
     */
    private $expiry;

    /**
     * @var mixed
     */
    private $defaultExpiry = '30 minutes';

    /**
     * @return string
     */
    public function key()
    {
        return 'token';
    }

    /**
     * @param  string $token The token.
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * @param  string $user The user.
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * @param  DateTimeInterface|string|null $expiry The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setExpiry($expiry)
    {
        if ($expiry === null) {
            $this->expiry = null;
            return $this;
        }

        if (is_string($expiry)) {
            try {
                $expiry = new DateTime($expiry);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }

        if (!($expiry instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Expiry" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->expiry = $expiry;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function expiry()
    {
        return $this->expiry;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->defaultExpiry = $container['admin/config']['login']['token_expiry'] ?? '2 hours';
    }

    /**
     * @see    \Charcoal\Source\StorableTrait::preSave() For the "create" Event.
     * @return boolean
     */
    protected function preSave()
    {
        if ($this->expiry === null) {
            $this->setExpiry('now +'.$this->defaultExpiry);
        }

        return parent::preSave();
    }
}
