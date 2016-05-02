<?php

namespace Charcoal\Admin\Object;

use \DateTime;
use \DateTimeInterface;

use \InvalidArgumentException;

use \Charcoal\Model\AbstractModel;

use \Charcoal\Admin\Object\AuthTokenMetadata;

/**
 *
 */
class AuthToken extends AbstractModel
{

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var string $token
     */
    private $token;

    /**
     * The username should be unique and mandatory.
     * @var string $username
     */
    private $username;

    /**
     * @var Datetime $expiry
     */
    private $expiry;

    /**
     * Token creation date (set automatically on save)
     * @var DateTime $Created
     */
    private $created;

    /**
     * Token last modified date (set automatically on save and update)
     * @var DateTime $LastModified
     */
    private $lastModified;

    /**
     * @return string
     */
    public function key()
    {
        return 'ident';
    }

    /**
     * @param string $ident The token ident.
     * @return AuthToken Chainable
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string $token The token.
     * @return AuthToken Chainable
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
     * Force a lowercase username
     *
     * @param string $username The username (also the login name).
     * @throws InvalidArgumentException If the username is not a string.
     * @return User Chainable
     */
    public function setUsername($username)
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException(
                'Set user username: Username must be a string'
            );
        }
        $this->username = mb_strtolower($username);
        return $this;
    }

    /**
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * @param DateTime|string|null $expiry The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return Content Chainable
     */
    public function setExpiry($expiry)
    {
        if ($expiry === null) {
            $this->expiry = null;
            return $this;
        }
        if (is_string($expiry)) {
            $expiry = new DateTime($expiry);
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
     * @return DateTime|null
     */
    public function expiry()
    {
        return $this->expiry;
    }

    /**
     * @param DateTime|string|null $created The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return Content Chainable
     */
    public function setCreated($created)
    {
        if ($created === null) {
            $this->created = null;
            return $this;
        }
        if (is_string($created)) {
            $created = new DateTime($created);
        }
        if (!($created instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Created" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->created = $created;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function created()
    {
        return $this->created;
    }

    /**
     * @param DateTime|string|null $lastModified The last modified date/time.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return Content Chainable
     */
    public function setLastModified($lastModified)
    {
        if ($lastModified === null) {
            $this->lastModified = null;
            return $this;
        }
        if (is_string($lastModified)) {
            $lastModified = new DateTime($lastModified);
        }
        if (!($lastModified instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Last Modified" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function lastModified()
    {
        return $this->lastModified;
    }

    /**
     * Note: the `random_bytes()` function is new to PHP-7. Available in PHP 5 with `compat-random`.
     *
     * @param string $username The username to generate the auth token from.
     * @return AuthToken Chainable
     */
    public function generate($username)
    {
        $this->setIdent(bin2hex(random_bytes(16)));
        $this->setToken(bin2hex(random_bytes(32)));
        $this->setUsername($username);
        $this->setExpiry('now + '.$this->metadata()->cookieDuration());

        return $this;
    }

    /**
     * @return AuthToken Chainable
     */
    public function sendCookie()
    {
        $name = $this->metadata()->cookieName();
        $value = $this->ident().';'.$this->token();
        $expiry = $this->expiry()->getTimestamp();
        $secure = $this->metadata()->httpsOnly();

        setcookie($name, $value, $expiry, '', '', $secure);

        return $this;
    }

     /**
      * StorableTrait > preSave(): Called automatically before saving the object to source.
      * @return boolean
      */
    public function preSave()
    {
        parent::preSave();

        if (password_needs_rehash($this->token, PASSWORD_DEFAULT)) {
            $this->token = password_hash($this->token, PASSWORD_DEFAULT);
        }
        $this->setCreated('now');
        $this->setLastModified('now');

        return true;
    }

    /**
     * StorableTrait > preUpdate(): Called automatically before updating the object to source.
     * @param array $properties The properties (ident) set for update.
     * @return boolean
     */
    public function preUpdate(array $properties = null)
    {
        parent::preUpdate($properties);

        $this->setLastModified('now');

        return true;
    }

    /**
     * @param mixed  $ident The user ident.
     * @param string $token The token.
     * @return mixed The user id.
     */
    public function getUserId($ident, $token)
    {
        $this->load($ident);
        if (!$this->ident()) {
            $this->logger->warning(sprintf('Auth token not found: "%s"', $ident));
            return '';
        }

        // Expired cookie
        $now = new DateTime('now');
        if (!$this->expiry() || $now > $this->expiry()) {
            $this->logger->warning('Expired auth token');
            $this->delete();
            return '';
        }

        // Validate encrypted token
        if (password_verify($token, $this->token()) !== true) {
            $this->panic();
            $this->delete();
            return '';
        }

        // Success!
        return $this->username();
    }

    /**
     * Something is seriously wrong: a cookie ident was in the database but with a tampered token.
     *
     * @return void
     */
    protected function panic()
    {
        // Todo: delete all user's token.
        // Gve a strongly-worded error message.

        $this->logger->error(
            'Possible security breach: an authentication token was found in the database but its token does not match.'
        );

        if ($this->username) {
            $table = $this->source()->table();
            $q = 'delete from '.$table.' where username = :username';
            $this->source()->dbQuery($q, ['username'=>$this->username()]);
        }
    }

    /**
     * DescribableTrait > create_metadata().
     *
     * @param array $data Optional data to intialize the Metadata object with.
     * @return MetadataInterface
     */
    protected function createMetadata(array $data = null)
    {
        $metadata = new AuthTokenMetadata();
        if ($data !== null) {
            $metadata->setData($data);
        }
        return $metadata;
    }
}
