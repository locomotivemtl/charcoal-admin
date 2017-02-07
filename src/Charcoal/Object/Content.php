<?php

namespace Charcoal\Object;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

// From `charcoal-translation`
use Charcoal\Translator\TranslatorAwareTrait;

// From `charcoal-object`
use Charcoal\Object\ContentInterface;
use Charcoal\Object\RevisionableInterface;
use Charcoal\Object\RevisionableTrait;

/**
 *
 */
class Content extends AbstractModel implements
    ContentInterface,
    RevisionableInterface
{
    use RevisionableTrait;
    use TranslatorAwareTrait;

    /**
     * Objects are active by default
     * @var boolean
     */
    private $active = true;

    /**
     * The position is used for ordering lists
     * @var integer
     */
    private $position = 0;

    /**
     * Object creation date (set automatically on save)
     * @var DateTimeInterface
     */
    private $created;

    /**
     * @var mixed
     */
    private $createdBy;

    /**
     * Object last modified date (set automatically on save and update)
     * @var DateTimeInterface
     */
    private $lastModified;

    /**
     * @var mixed
     */
    private $lastModifiedBy;

    /**
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setTranslator($container['translator']);
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return Content Chainable
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory()
    {
        return $this->modelFactory;
    }

    /**
     * @param boolean $active The active flag.
     * @return Content Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * @return boolean
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * @param integer $position The position (for ordering purpose).
     * @throws InvalidArgumentException If the position is not an integer (or numeric integer string).
     * @return Content Chainable
     */
    public function setPosition($position)
    {
        if ($position === null) {
            $this->position = null;
            return $this;
        }

        if (!is_numeric($position)) {
            throw new InvalidArgumentException(sprintf(
                'Position must be an integer, received %s',
                is_object($position) ? get_class($position) : gettype($position)
            ));
        }

        $this->position = (int)$position;
        return $this;
    }

    /**
     * @return integer
     */
    public function position()
    {
        return $this->position;
    }

    /**
     * @param \DateTimeInterface|string|null $created The date/time at object's creation.
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
     * @return DateTimeInterface|null
     */
    public function created()
    {
        return $this->created;
    }

    /**
     * @param mixed $createdBy The creator of the content object.
     * @return Content Chainable
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function createdBy()
    {
        return $this->createdBy;
    }

    /**
     * @param \DateTimeInterface|string|null $lastModified The last modified date/time.
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
     * @return DateTimeInterface
     */
    public function lastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param mixed $lastModifiedBy The last modification's username.
     * @return Content Chainable
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function lastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * StorableTrait > preSavƒe(): Called automatically before saving the object to source.
     * For content object, set the `created` and `lastModified` properties automatically
     * @return boolean
     */
    public function preSave()
    {
        parent::preSave();

        $this->setCreated('now');
        $this->setLastModified('now');

        return true;
    }

    /**
     * StorableTrait > preUpdate(): Called automatically before updating the object to source.
     * For content object, set the `lastModified` property automatically.
     *
     * @param array $properties The properties (ident) set for update.
     * @return boolean
     */
    public function preUpdate(array $properties = null)
    {
        parent::preUpdate($properties);

        // Content is revisionable
        if ($this->revisionEnabled()) {
            $this->generateRevision();
        }

        $this->setLastModified('now');

        return true;
    }
}
