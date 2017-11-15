<?php

namespace Charcoal\Object;

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
use Charcoal\Object\AuthorableInterface;
use Charcoal\Object\AuthorableTrait;
use Charcoal\Object\RevisionableInterface;
use Charcoal\Object\RevisionableTrait;
use Charcoal\Object\TimestampableInterface;
use Charcoal\Object\TimestampableTrait;

/**
 *
 */
class Content extends AbstractModel implements
    AuthorableInterface,
    ContentInterface,
    RevisionableInterface,
    TimestampableInterface
{
    use AuthorableTrait;
    use RevisionableTrait;
    use TranslatorAwareTrait;
    use TimestampableTrait;

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
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * @var string[]
     */
    private $requiredAclPermissions = [];

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
     * @throws InvalidArgumentException If the ACL permissions are invalid.
     * @param  string|string[] $permissions The required ACL permissions.
     * @return Content Chainable
     */
    public function setRequiredAclPermissions($permissions)
    {
        if ($permissions === null || !$permissions) {
            $this->requiredAclPermissions = [];
            return $this;
        }
        if (is_string($permissions)) {
            $permissions = explode(',', $permissions);
            $permissions = array_map('trim', $permissions);
        }
        if (!is_array($permissions)) {
            throw new InvalidArgumentException(
                sprintf('Invalid ACL permissions. Permissions need to be an array (%s given)', gettype($permissions))
            );
        }
        $this->requiredAclPermissions = $permissions;
        return $this;
    }

    /**
     * @return string[]
     */
    public function requiredAclPermissions()
    {
        return $this->requiredAclPermissions;
    }

    /**
     * StorableTrait > preSave(): Called automatically before saving the object to source.
     * For content object, set the `created` and `lastModified` properties automatically
     * @return boolean
     */
    protected function preSave()
    {
        parent::preSave();

        // Timestampable properties
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
    protected function preUpdate(array $properties = null)
    {
        parent::preUpdate($properties);

        // Content is revisionable
        if ($this->revisionEnabled()) {
            $this->generateRevision();
        }

        // Timestampable propertiees
        $this->setLastModified('now');

        return true;
    }
}
