<?php

namespace Charcoal\Object\Tests\Mocks;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalInterface;
use Charcoal\Object\HierarchicalTrait;

/**
 *
 */
class HierarchicalClass implements
    ModelInterface,
    HierarchicalInterface
{
    use HierarchicalTrait;

    private $id;

    public function __construct($id = null)
    {
        if ($id === null) {
            $id = uniqid();
        }

        $this->setId($id);
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function id()
    {
        return $this->id;
    }

    public function objType()
    {
        return 'charcoal/tests/object/hierarchical-class';
    }

    public function loadChildren()
    {
        return [];
    }

    public function modelFactory()
    {
        return null;
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
