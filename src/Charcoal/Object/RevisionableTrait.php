<?php

namespace Charcoal\Object;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevision;
use Charcoal\Object\ObjectRevisionInterface;

/**
 *
 */
trait RevisionableTrait
{
    /**
     * @var boolean $revisionEnabled
     */
    protected $revisionEnabled = true;

    /**
     * The class name of the object revision model.
     *
     * Must be a fully-qualified PHP namespace and an implementation of
     * {@see \Charcoal\Object\ObjectRevisionInterface}. Used by the model factory.
     *
     * @var string
     */
    private $objectRevisionClass = ObjectRevision::class;

    /**
     * @param  boolean $enabled The (revision) enabled flag.
     * @return RevisionableInterface Chainable
     */
    public function setRevisionEnabled($enabled)
    {
        $this->revisionEnabled = !!$enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRevisionEnabled()
    {
        return $this->revisionEnabled;
    }

    /**
     * Create a revision object.
     *
     * @return ObjectRevisionInterface
     */
    public function createRevisionObject()
    {
        $rev = $this->modelFactory()->create($this->getObjectRevisionClass());

        return $rev;
    }

    /**
     * Set the class name of the object revision model.
     *
     * @param  string $className The class name of the object revision model.
     * @throws InvalidArgumentException If the class name is not a string.
     * @return AbstractPropertyDisplay Chainable
     */
    protected function setObjectRevisionClass($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Route class name must be a string.'
            );
        }

        $this->objectRevisionClass = $className;
        return $this;
    }

    /**
     * Retrieve the class name of the object revision model.
     *
     * @return string
     */
    public function getObjectRevisionClass()
    {
        return $this->objectRevisionClass;
    }

    /**
     * Alias of {@see self::getObjectRevisionClass()}.
     *
     * @return string
     */
    public function objectRevisionClass()
    {
        return $this->getObjectRevisionClass();
    }

    /**
     * @see \Charcoal\Object\ObjectRevision::create_fromObject()
     * @return ObjectRevision
     */
    public function generateRevision()
    {
        $rev = $this->createRevisionObject();

        $rev->createFromObject($this);
        if (!empty($rev->getDataDiff())) {
            $rev->save();
        }

        return $rev;
    }

    /**
     * @see \Charcoal\Object\ObejctRevision::lastObjectRevision
     * @return ObjectRevision
     */
    public function latestRevision()
    {
        $rev = $this->createRevisionObject();
        $rev = $rev->lastObjectRevision($this);

        return $rev;
    }

    /**
     * @see \Charcoal\Object\ObejctRevision::objectRevisionNum
     * @param  integer $revNum The revision number.
     * @return ObjectRevision
     */
    public function revisionNum($revNum)
    {
        $rev = $this->createRevisionObject();
        $rev = $rev->objectRevisionNum($this, intval($revNum));

        return $rev;
    }

    /**
     * Retrieves all revisions for the current objet
     *
     * @param  callable $callback Optional object callback.
     * @return array
     */
    public function allRevisions(callable $callback = null)
    {
        $loader = new CollectionLoader([
            'logger'  => $this->logger,
            'factory' => $this->modelFactory(),
        ]);
        $loader->setModel($this->createRevisionObject());
        $loader->addFilter('targetType', $this->objType());
        $loader->addFilter('targetId', $this->id());
        $loader->addOrder('revTs', 'desc');
        if ($callback !== null) {
            $loader->setCallback($callback);
        }
        $revisions = $loader->load();

        return $revisions->objects();
    }

    /**
     * @param  integer $revNum The revision number to revert to.
     * @throws InvalidArgumentException If revision number is invalid.
     * @return boolean Success / Failure.
     */
    public function revertToRevision($revNum)
    {
        if (!$revNum) {
            throw new InvalidArgumentException(
                'Invalid revision number'
            );
        }

        $rev = $this->revisionNum(intval($revNum));

        if (!$rev->id()) {
            return false;
        }

        if (isset($obj['lastModifiedBy'])) {
            $obj['lastModifiedBy'] = $rev->getRevUser();
        }

        $this->setData($rev->getDataObj());
        $this->update();

        return true;
    }

    /**
     * Retrieve the object model factory.
     *
     * @return \Charcoal\Factory\FactoryInterface
     */
    abstract public function modelFactory();

    /**
     * @return \Charcoal\Model\MetadataInterface
     */
    abstract public function metadata();
}
