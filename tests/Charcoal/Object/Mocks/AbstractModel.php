<?php

namespace Charcoal\Tests\Object\Mocks;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

/**
 *
 */
abstract class AbstractModel
{
    // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found
    use TranslatorAwareTrait;

    /**
     * @var string|integer
     */
    private $id;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var FactoryInterface
     */
    protected $modelFactory;

    /**
     * @param array|null $data The model dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['factory'])) {
            $this->setModelFactory($data['factory']);
        }

        if (isset($data['translator'])) {
            $this->setTranslator($data['translator']);
        }

        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
    }

    /**
     * @param  string|integer $id The model ID.
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|integer
     */
    public function getId()
    {
        if ($this->id === null) {
            $this->id = uniqid();
        }

        return $this->id;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'id';
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->getKey();
    }

    /**
     * @return string
     */
    abstract public function objType();

    /**
     * @param  FactoryInterface $factory The model factory.
     * @return self
     */
    public function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;

        return $this;
    }

    /**
     * @return FactoryInterface
     */
    public function modelFactory()
    {
        return $this->modelFactory;
    }

    /**
     * @param  array $metadata The model metadata.
     * @return self
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return array
     */
    public function metadata()
    {
        if ($this->metadata === null) {
            return [];
        }
        return $this->metadata;
    }

    /**
     * @param  array $data The model data.
     * @return self
     */
    public function setData(array $data)
    {
        return null;
    }

    /**
     * @param  array|null $filters Retrieve a subset.
     * @return array
     */
    public function data(array $filters = null)
    {
        return null;
    }

    /**
     * @param  array $data The odel flat data.
     * @return self
     */
    public function setFlatData(array $data)
    {
        return null;
    }

    /**
     * @return array
     */
    public function flatData()
    {
        return null;
    }

    /**
     * @return array
     */
    public function defaultData()
    {
        return null;
    }

    /**
     * @param  array $propertyIdents Optional. List of property identifiers
     *     for retrieving a subset of property objects.
     * @return null
     */
    public function properties(array $propertyIdents = null)
    {
        return null;
    }

    /**
     * @param  string $propertyIdent The property (ident) to get.
     * @return PropertyInterface
     */
    public function property($propertyIdent)
    {
        return null;
    }

    /**
     * @param  string $propertyIdent The property (ident) to check.
     * @return boolean
     */
    public function hasProperty($propertyIdent)
    {
        return false;
    }

    /**
     * Alias of `properties()` (if not parameter is set) or `property()`.
     *
     * @param  string $propertyIdent The property (ident) to get.
     * @return mixed
     */
    public function p($propertyIdent = null)
    {
        return null;
    }
    // phpcs:enable
}
