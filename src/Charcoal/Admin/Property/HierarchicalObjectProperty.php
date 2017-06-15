<?php

namespace Charcoal\Admin\Property;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

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
     * Retrieve the choices segmented as a tree.
     *
     * @return array
     */
    public function choices()
    {
        $loader = $this->collectionLoader();
        $orders = $this->orders();
        if ($orders) {
            $loader->setOrders($orders);
        }

        $filters = $this->filters();
        if ($filters) {
            $loader->setFilters($filters);
        }

        $collection = new HierarchicalCollection($loader->load(), false);
        $collection
            ->setPage($loader->page())
            ->setNumPerPage($loader->numPerPage())
            ->sortTree();

        $choices = [];
        foreach ($collection as $obj) {
            $choice = $this->choice($obj);

            if ($choice !== null) {
                $choices[$obj->id()] = $choice;
            }
        }

        return $choices;
    }

    /**
     * Returns a choice structure for a given ident.
     *
     * @param string|ModelInterface $choiceIdent The choice ident or object to format.
     * @return mixed The matching choice.
     */
    public function choice($choiceIdent)
    {
        $obj = $this->loadObject($choiceIdent);

        if ($obj === null) {
            return null;
        }

        $choice = parent::choice($obj);

        if (property_exists($obj, 'auxiliary') && $obj->auxiliary) {
            $choice['parent'] = true;
        } else {
            $choice['group'] = ($obj->hasMaster() ? $obj->master()->id() : null);
        }

        if (is_callable([ $obj, 'name' ])) {
            $choice['title'] = $obj->name();
        } elseif (is_callable([ $obj, 'label' ])) {
            $choice['title'] = $obj->label();
        } elseif (is_callable([ $obj, 'title' ])) {
            $choice['title'] = $obj->title();
        }

        return $choice;
    }
}
