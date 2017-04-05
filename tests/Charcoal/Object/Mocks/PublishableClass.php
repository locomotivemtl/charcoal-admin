<?php

namespace Charcoal\Object\Tests\Mocks;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-object'
use Charcoal\Object\PublishableInterface;
use Charcoal\Object\PublishableTrait;

/**
 *
 */
class PublishableClass implements
    ModelInterface,
    PublishableInterface
{
    use PublishableTrait;
    use TranslatorAwareTrait;

    private $id;

    protected $metadata;

    protected $modelFactory;

    public function __construct(array $data = null)
    {
        $this->setModelFactory($data['factory']);
        $this->setTranslator($data['translator']);

        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function id()
    {
        if ($this->id === null) {
            $this->id = uniqid();
        }

        return $this->id;
    }

    public function key()
    {
        return 'id';
    }

    public function objType()
    {
        return 'charcoal/tests/object/publishable-class';
    }

    public function setModelFactory($factory)
    {
        $this->modelFactory = $factory;

        return $this;
    }

    public function modelFactory()
    {
        return $this->modelFactory;
    }

    public function setMetadata($metadata)
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
     * @param array $data The model data.
     * @return ModelInterface Chainable
     */
    public function setData(array $data)
    {
        return null;
    }

    /**
     * @return array
     */
    public function data()
    {
        return null;
    }

    /**
     * @param array $data The odel flat data.
     * @return ModelInterface Chainable
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
     * @return array
     */
    public function properties()
    {
        return null;
    }

    /**
     * @param string $propertyIdent The property (ident) to get.
     * @return PropertyInterface
     */
    public function property($propertyIdent)
    {
        return null;
    }

    /**
     * Alias of `properties()` (if not parameter is set) or `property()`.
     *
     * @param string $propertyIdent The property (ident) to get.
     * @return mixed
     */
    public function p($propertyIdent = null)
    {
        return null;
    }
}
