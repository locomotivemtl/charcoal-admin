<?php

namespace Charcoal\Admin\Ui;

use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Loader\CollectionLoader as CollectionLoader;
use \Charcoal\Model\Collection as Collection;
use \Charcoal\Model\ModelFactory as ModelFactory;

/**
* Fully implements CollectionContainerInterface
*/
trait CollectionContainerTrait
{
    /**
    * @var string $_obj_type
    */
    protected $_obj_type;
    /**
    * @var string $_collection_ident
    */
    protected $_collection_ident;
    /**
    * @var mixed $_collection_config
    */
    protected $_collection_config;
    /**
    * @var Collection $_collection
    */
    protected $_collection;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_data($data)
    {

        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        if (isset($data['obj_type']) && $data['obj_type'] !== null) {
            $this->set_obj_type($data['obj_type']);
        }
        if (isset($data['collection_config']) && $data['collection_config'] !== null) {
            $this->set_collection_config($data['collection_config']);
        }

        return $this;
    }

    /**
    * @param string $obj_type
    * @throws InvalidArgumentException if provided argument is not of type 'string'.
    * @return CollectionContainerInterface Chainable
    */
    public function set_obj_type($obj_type)
    {
        if (!is_string($obj_type)) {
            throw new InvalidArgumentException('Obj type must be a string');
        }
        $this->_obj_type = str_replace(['.', '_'], '/', $obj_type);
        return $this;
    }

    /**
    * @return string
    */
    public function obj_type()
    {
        return $this->_obj_type;
    }

    /**
    * @param mixed $dashboard_config
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_config($collection_config)
    {
        $this->_collection_config = $collection_config;
        return $this;
    }

    /**
    * @return mixed
    */
    public function collection_config()
    {
        if ($this->_collection_config === null) {
            $this->_collection_config = $this->create_collection_config();
        }
        return $this->_collection_config;
    }

    /**
    * @param array $data
    * @return mixed
    */
    public function create_collection_config($data = null)
    {
        unset($data);
        return [];
    }


    /**
    * @param mixed $collection
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
    * @return Collection
    */
    public function collection()
    {
        if ($this->_collection === null) {
            $this->_collection = $this->create_collection();
        }
        return $this->_collection;
    }

    /**
    * @todo Integrate $data; merge with $collection_config
    * @param array $data Optional
    * @throws Exception
    * @return CollectionLoader
    */
    public function create_collection(array $data = null)
    {
        unset($data);
        $obj_type = $this->obj_type();
        if (!$obj_type) {
            throw new Exception(__CLASS__.'::'.__FUNCTION__.' - Can not create collection, object type is not defined.');
        }
        $obj = ModelFactory::instance()->get($obj_type);

        $loader = new CollectionLoader();
        $loader->set_model($obj);
        $collection_config = $this->collection_config();
        if (is_array($collection_config) && !empty($collection_config)) {
            $loader->set_data($collection_config);
        }

        $collection = $loader->load();
        return $collection;
    }

    /**
    * @return array
    */
    public function objects()
    {
        $collection = $this->collection();
        return $this->collection()->objects();
    }

    /**
    * Supplies properties for objects in table template specific to object configuration
    * @return  Generator
    */
    public function object_rows()
    {

        // Get properties as defined in object's list metadata
        $sorted_properties = $this->properties();

        // Collection objects
        $objects = $this->objects();

        // Go through each object to generate an array of properties listed in object's list metadata
        foreach ($objects as $object) {
            $object_properties = [];

            foreach ($sorted_properties as $property_ident => $property_data) {
                $property = $object->property($property_ident);
                $property_value = $property->val();

                if ($property->l10n() === true) {
                    $property_value = $property_value['fr'];
                }
                if ($property->multiple() === true) {
                    if (is_array($property_value)) {
                        $property_value = implode(',', $property_value);
                    }
                }
                $object_properties[] = [
                    'ident' => $property_ident,
                    'val'   => $property_value
                ];
            };

            $row = [
                'object_id' => $object->id(),
                'object_properties' => $object_properties
            ];

            yield $row;
        }
    }

    /**
    * @return Boolean
    */
    public function has_objects()
    {
        return (count($this->objects()) > 0);
    }

    /**
    * @return Object
    */
    public function proto()
    {
        $obj_type = $this->obj_type();
        $obj = ModelFactory::instance()->get($obj_type);
        return $obj;
    }

}
