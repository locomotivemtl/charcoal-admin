<?php

namespace Charcoal\Admin\Property;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalInterface;

// From 'charcoal-property'
use Charcoal\Property\ObjectProperty;

// From 'charcoal-support'
use Charcoal\Object\HierarchicalCollection;

/**
 * Hierarchical Object Property
 */
class HierarchicalObjectProperty extends ObjectProperty
{
    /**
     * The property's object id.
     *
     * @var string|integer
     */
    private $objId;

    /**
     * Retrieve the available choice structures, segmented as a tree.
     *
     * @return array
     */
    public function choices()
    {
        $choices = [];

        $proto = $this->proto();
        if (!$proto->source()->tableExists()) {
            return $choices;
        }

        $loader = $this->collectionModelLoader();

        $collection = new HierarchicalCollection($loader->load(), false);
        $collection->setPage($loader->page())
                   ->setNumPerPage($loader->numPerPage())
                   ->sortTree();

        return $this->parseChoices($collection);
    }

    /**
     * Parse the given value into a choice structure.
     *
     * @param  ModelInterface $obj An object to format.
     * @return array Returns a choice structure.
     */
    protected function parseChoice(ModelInterface $obj)
    {
        $choice = parent::parseChoice($obj);

        if (property_exists($obj, 'auxiliary') && $obj->auxiliary) {
            $choice['parent'] = true;
        } elseif ($obj instanceof HierarchicalInterface && $obj->hasMaster()) {
            $choice['group'] = parent::parseChoice($obj['masterObject']);
        } else {
            $choice['group'] = null;
        }

        if (isset($obj['name'])) {
            $choice['title'] = $obj['name'];
        } elseif (isset($obj['label'])) {
            $choice['title'] = $obj['label'];
        } elseif (isset($obj['title'])) {
            $choice['title'] = $obj['title'];
        }

        return $choice;
    }


    /**
     * @return integer|string The property object's id.
     */
    public function objId()
    {
        return $this->objId;
    }

    /**
     * @param integer|string $objId ObjId for ObjectProperty.
     * @return self
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }
}
