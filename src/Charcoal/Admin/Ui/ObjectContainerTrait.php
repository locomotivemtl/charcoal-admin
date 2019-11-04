<?php

namespace Charcoal\Admin\Ui;

use Exception;
use RuntimeException;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewInterface;

/**
 * An implementation, as Trait, of the {@see \Charcoal\Admin\Ui\ObjectContainerInterface}.
 */
trait ObjectContainerTrait
{
    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @var string|null $objType
     */
    protected $objType;

    /**
     * @var string|numeric|null $objId
     */
    protected $objId;

    /**
     * @var string $objBaseClass
     */
    protected $objBaseClass;

    /**
     * @var ModelInterface $obj
     */
    protected $obj;

    /**
     * Holds a list of all renderable classes.
     *
     * Format: `class => boolean`
     *
     * @var boolean[]
     */
    protected static $objRenderableCache = [];

    /**
     * @param FactoryInterface $factory The model factory, to create objects.
     * @return ObjectContainerInterface Chainable
     */
    public function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the model factory was not set before being accessed.
     * @return FactoryInterface
     */
    protected function modelFactory()
    {
        if ($this->modelFactory === null) {
            throw new Exception(sprintf(
                'Model factory not set for %s',
                get_class($this)
            ));
        }
        return $this->modelFactory;
    }

    /**
     * Set the object type.
     *
     * @param string $objType The object type.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(sprintf(
                'Object type must be a string, received %s.',
                (is_object($objType) ? get_class($objType) : gettype($objType))
            ));
        }

        $this->objType = str_replace(['.', '_'], '/', $objType);

        return $this;
    }

    /**
     * Retrieve the object type.
     *
     * @return string|null
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * Set the object ID.
     *
     * @param  string|numeric $objId The object ID to load.
     * @throws InvalidArgumentException If provided argument is not of type 'scalar'.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjId($objId)
    {
        if (!is_scalar($objId)) {
            throw new InvalidArgumentException(sprintf(
                'Object ID must be a string or numerical value, received %s.',
                (is_object($objId) ? get_class($objId) : gettype($objId))
            ));
        }

        $this->objId = $objId;

        return $this;
    }

    /**
     * Retrieve the object ID.
     *
     * @return string|numeric|null
     */
    public function objId()
    {
        return $this->objId;
    }

    /**
     * Retrieve the object ID and addslashes.
     * Used in template/charcoal/admin/template/form/js.mustache to
     * prevent problems with ID with single or double quotes.
     *
     * @return string Escaped ID.
     */
    public function objIdWithSlashes()
    {
        return addslashes($this->objId());
    }

    /**
     * @param string $objBaseClass The base class.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjBaseClass($objBaseClass)
    {
        if (!is_string($objBaseClass)) {
            throw new InvalidArgumentException(
                'Base class must be a string.'
            );
        }
        $this->objBaseClass = $objBaseClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function objBaseClass()
    {
        return $this->objBaseClass;
    }

    /**
     * Retrieve a singleton of the {self::$objType} for prototyping.
     *
     * @throws RuntimeException If the class has no object type.
     * @return ModelInterface
     */
    public function proto()
    {
        $objType = $this->objType();
        if (!$objType) {
            throw new RuntimeException('Can not create prototype, object type is undefined');
        }

        return $this->modelFactory()->get($objType);
    }

    /**
     * Determine if the class has a concrete object.
     *
     * @return boolean
     */
    public function hasObj()
    {
        return ($this->obj() && $this->obj()->id());
    }

    /**
     * Retrieve the object.
     *
     * @return ModelInterface
     */
    public function obj()
    {
        if ($this->obj === null) {
            $this->obj = $this->createOrLoadObj();

            if ($this->obj instanceof ModelInterface) {
                $this->objId = $this->obj->id();
            } else {
                $this->objId = null;
            }
        }

        return $this->obj;
    }

    /**
     * Create or load the object.
     *
     * @return ModelInterface
     */
    protected function createOrLoadObj()
    {
        if ($this->objId()) {
            return $this->loadObj();
        } elseif (!empty($_GET['clone_id'])) {
            return $this->cloneObj();
        } elseif (!empty($_GET['blueprint_id'])) {
            return $this->createObjFromBluePrint();
        } else {
            return $this->createObj();
        }
    }

    /**
     * @throws Exception If the object is not valid.
     * @return ModelInterface
     */
    protected function cloneObj()
    {
        $cloneId = filter_input(INPUT_GET, 'clone_id', FILTER_SANITIZE_ENCODED);

        if (empty($cloneId)) {
            throw new Exception(sprintf(
                '%1$s cannot clone object. Clone ID missing from request.',
                get_class($this)
            ));
        }

        $obj   = $this->createObj();
        $clone = $this->createObj()->load($cloneId);

        $data = $clone->data();
        unset($data[$clone->key()]);

        // This is the actual key we want to remove.
        unset($data[$obj->key()]);

        // Set the right data
        $obj->setData($data);

        return $obj;
    }

    /**
     * @throws Exception If the object is not valid.
     * @return ModelInterface
     */
    protected function createObjFromBluePrint()
    {
        $bpId = filter_input(INPUT_GET, 'blueprint_id', FILTER_SANITIZE_ENCODED);

        if (empty($bpId)) {
            throw new Exception(sprintf(
                '%1$s cannot create object from blueprint. Blueprint ID missing from request.',
                get_class($this)
            ));
        }

        $obj = $this->createObj();

        $blueprint = $this->modelFactory()->create($obj->blueprintType());
        $blueprint->load($bpId);

        $data = $blueprint->data();
        unset($data[$blueprint->key()]);

        // This is the actual key we want to remove.
        unset($data[$obj->key()]);

        // Set the right data
        $obj->setData($data);

        return $obj;
    }

    /**
     * @throws Exception If the object is not valid.
     * @return ModelInterface
     */
    protected function createObj()
    {
        if (!$this->validateObjType()) {
            throw new Exception(sprintf(
                '%1$s cannot create object. Invalid object type: "%2$s"',
                get_class($this),
                $this->objType()
            ));
        }

        $objType = $this->objType();
        $obj = $this->modelFactory()->create($objType);

        return $obj;
    }

    /**
     * @return ModelInterface The loaded object
     */
    protected function loadObj()
    {
        if ($this->obj === null) {
            $this->obj = $this->createObj();
        }
        $obj = $this->obj;

        $objId = $this->objId();
        if (!$objId) {
            return $obj;
        }
        $obj->load($objId);
        return $obj;
    }

    /**
     * @throws RuntimeException If the object is invalid.
     * @return boolean
     */
    protected function validateObjType()
    {
        try {
            $objType = $this->objType();
            if (!$objType) {
                return false;
            }

            // Catch exception to know if the objType is valid
            $obj = $this->proto();
            if (!$this->validateObjBaseClass($obj)) {
                throw new RuntimeException(sprintf(
                    'Can not create object, type is not an instance of %s',
                    $this->objBaseClass()
                ));
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed $obj Object to validate.
     * @return boolean
     */
    protected function validateObjBaseClass($obj)
    {
        $objBaseClass = $this->objBaseClass();
        if (!$objBaseClass) {
            // If no base class is set, then no validation is performed.
            return true;
        }

        try {
            return ($obj instanceof $objBaseClass);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Determine if the model implements {@see \Charcoal\View\ViewableInterface}.
     *
     * @param  string|object $obj      Object type or instance to test.
     * @param  boolean       $toString Whether to test for `__toString()`.
     * @return boolean
     */
    protected function isObjRenderable($obj, $toString = false)
    {
        if (is_string($obj)) {
            if (!method_exists($this, 'modelFactory')) {
                return false;
            }

            $obj = $this->modelFactory()->get($obj);
        }

        if (!is_object($obj)) {
            return false;
        }

        $key = get_class($obj);

        if (isset(static::$objRenderableCache[$key])) {
            return static::$objRenderableCache[$key];
        }

        $check = false;
        if (is_object($obj)) {
            if (($obj instanceof ViewableInterface) && ($obj->view() instanceof ViewInterface)) {
                $check = true;
            } elseif ($toString && is_callable([ $obj, '__toString()' ])) {
                $check = true;
            }
        }

        static::$objRenderableCache[$key] = $check;

        return static::$objRenderableCache[$key];
    }
}
