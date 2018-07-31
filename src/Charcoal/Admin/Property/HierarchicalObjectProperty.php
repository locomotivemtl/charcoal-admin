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

// local dependencies
use Charcoal\Admin\Support\HttpAwareTrait;

/**
 * Hierarchical Object Property
 */
class HierarchicalObjectProperty extends ObjectProperty
{
    use HttpAwareTrait;

    const DATA_SOURCE_REQUEST = 'request';
    const DATA_SOURCE_OBJECT  = 'object';

    /**
     * The property's object id.
     *
     * @var string|int
     */
    private $objId;

    /**
     * @param array $data The widget data.
     * @return self
     */
    public function setData(array $data)
    {
        parent::setData($data);

        $this->mergeDataSources($data);

        return $this;
    }

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
            $choice['group'] = parent::parseChoice($obj->master());
        } else {
            $choice['group'] = null;
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

    /**
     * Retrieve the available data sources (when setting data on an entity).
     *
     * @param array|mixed $dataset The entity data.
     * @return self
     */
    protected function mergeDataSources($dataset = null)
    {
        $sources = [static::DATA_SOURCE_REQUEST];
        foreach ($sources as $sourceIdent) {
            $getter = $this->camelize('data_from_'.$sourceIdent);
            $method = [ $this, $getter ];

            if (is_callable($method)) {
                $data = call_user_func($method);

                if ($data) {
                    parent::setData($data);
                }
            }
        }

        return $this;
    }

    /**
     * Fetch metadata from the current request.
     *
     * @return array
     */
    protected function dataFromRequest()
    {
        return $this->httpRequest()->getParams($this->acceptedRequestData());
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    protected function acceptedRequestData()
    {
        return [
            'obj_id'
        ];
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

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setHttpRequest($container['request']);
    }
}
