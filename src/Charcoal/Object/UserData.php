<?php

namespace Charcoal\Object;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-object'
use Charcoal\Object\UserDataInterface;

/**
 * User Data is a base model for objects typically submitted by the end-user of the application.
 *
 * Although it is not abstract, it is typically used by extending into a subclass.
 */
class UserData extends AbstractModel implements
    UserDataInterface
{
    use TranslatorAwareTrait;

    /**
     * Client IP address of the end-user.
     *
     * @var integer|null
     */
    private $ip;

    /**
     * Language of the end-user or source URI.
     *
     * @var string|null
     */
    private $lang;

    /**
     * Source URL or identifier of end-user submission.
     *
     * @var string|null
     */
    private $origin;

    /**
     * Creation timestamp of submission.
     *
     * @var DateTimeInterface|null
     */
    private $ts;

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setTranslator($container['translator']);
    }

    /**
     * Set the client IP address.
     *
     * @param  integer|null $ip The remote IP at object creation.
     * @return self
     */
    public function setIp($ip)
    {
        if ($ip === null) {
            $this->ip = null;
            return $this;
        }

        if (is_string($ip)) {
            $ip = ip2long($ip);
        } elseif (is_numeric($ip)) {
            $ip = intval($ip);
        } else {
            $ip = 0;
        }

        $this->ip = $ip;

        return $this;
    }

    /**
     * Retrieve the client IP address.
     *
     * @return integer|null
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set the origin language.
     *
     * @param  string $lang The language code.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setLang($lang)
    {
        if ($lang !== null) {
            if (!is_string($lang)) {
                throw new InvalidArgumentException(
                    'Language must be a string'
                );
            }
        }

        $this->lang = $lang;

        return $this;
    }

    /**
     * Retrieve the language.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set the origin of the object submission.
     *
     * @param  string $origin The source URL or identifier of the submission.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setOrigin($origin)
    {
        if ($origin !== null) {
            if (!is_string($origin)) {
                throw new InvalidArgumentException(
                    'Origin must be a string.'
                );
            }
        }

        $this->origin = $origin;

        return $this;
    }

    /**
     * Resolve the origin of the user data.
     *
     * @return string
     */
    public function resolveOrigin()
    {
        $host = getenv('HTTP_HOST');
        $uri  = '';
        if ($host) {
            $uri = 'http';

            if (getenv('HTTPS') === 'on') {
                $uri .= 's';
            }

            $uri .= '://'.$host;
        }
        $uri .= getenv('REQUEST_URI');

        return $uri;
    }

    /**
     * Retrieve the origin of the object submission.
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set when the object was created.
     *
     * @param  DateTimeInterface|string|null $timestamp The timestamp at object's creation.
     *     NULL is accepted and instances of DateTimeInterface are recommended;
     *     any other value will be converted (if possible) into one.
     * @throws InvalidArgumentException If the timestamp is invalid.
     * @return self
     */
    public function setTs($timestamp)
    {
        if ($timestamp === null) {
            $this->ts = null;
            return $this;
        }

        if (is_string($timestamp)) {
            try {
                $timestamp = new DateTime($timestamp);
            } catch (Exception $e) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid timestamp: %s',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        if (!$timestamp instanceof DateTimeInterface) {
            throw new InvalidArgumentException(
                'Invalid timestamp value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->ts = $timestamp;

        return $this;
    }

    /**
     * Retrieve the creation timestamp.
     *
     * @return DateTimeInterface|null
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Event called before _creating_ the object.
     *
     * @see    Charcoal\Source\StorableTrait::preSave() For the "create" Event.
     * @return boolean
     */
    protected function preSave()
    {
        $result = parent::preSave();

        $this->setTs('now');

        if (getenv('REMOTE_ADDR')) {
            $this->setIp(getenv('REMOTE_ADDR'));
        }

        if (!isset($this->origin)) {
            $this->setOrigin($this->resolveOrigin());
        }

        return $result;
    }
}
