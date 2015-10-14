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
    * @var string $obj_type
    */
    private $obj_type;

    /**
    * @var string $collection_ident
    */
    private $collection_ident;

    /**
    * @var mixed $collection_config
    */
    private $collection_config;

    /**
    * @var integer $page
    */
    private $page = 1;

    /**
    * @var integer $num_per_page
    */
    private $num_per_page = 50;


    /**
    * @var Collection $collection
    */
    private $collection;

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
        $this->obj_type = str_replace(['.', '_'], '/', $obj_type);
        return $this;
    }

    /**
    * @return string
    */
    public function obj_type()
    {
        return $this->obj_type;
    }

    /**
    * @param string $collection_ident
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_ident($collection_ident)
    {
        if (!is_string($collection_ident)) {
            throw new InvalidArgumentException(
                'Collection identifier must be a string'
            );
        }
        $this->collection_ident = $collection_ident;
        return $this;
    }

    /**
    * @return string|null
    */
    public function collection_ident()
    {
        return $this->collection_ident;
    }

    /**
    * @param mixed $dashboard_config
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_config($collection_config)
    {
        $this->collection_config = $collection_config;
        return $this;
    }

    /**
    * @return mixed
    */
    public function collection_config()
    {
        if ($this->collection_config === null) {
            $this->collection_config = $this->create_collection_config();
        }
        return $this->collection_config;
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
    * @param integer $page
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_page($page)
    {
        if (!is_int($page)) {
            throw new InvalidArgumentException(
                'Page must be an integer value.'
            );
        }
        if ($page < 1) {
            throw new InvalidArgumentException(
                'Page must be 1 or greater.'
            );
        }
        $this->page = $page;
        return $this;
    }

    /**
    * @return integer
    */
    public function page()
    {
        return $this->page;
    }

    /**
    * @param integer $num_per_page
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_num_per_page($num_per_page)
    {
        if (!is_int($num_per_page)) {
            throw new InvalidArgumentException(
                'Num per page must be an integer value.'
            );
        }
        if ($num_per_page < 1) {
            throw new InvalidArgumentException(
                'Num per page must be 1 or greater.'
            );
        }
        $this->num_per_page = $num_per_page;
        return $this;
    }

    /**
    * @return integer
    */
    public function num_per_page()
    {
        return $this->num_per_page;
    }

    /**
    * @param mixed $collection
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
    * @return Collection
    */
    public function collection()
    {
        if ($this->collection === null) {
            $this->collection = $this->create_collection();
        }
        return $this->collection;
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

        $loader->set_pagination([
            'page'=>$this->page(),
            'num_per_page'=>$this->num_per_page()
        ]);

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
                $property_value = $property->display_val();

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
    * @return integer
    */
    public function num_objects()
    {
        return count($this->objects());
    }

    /**
    * @return Object
    */
    public function proto()
    {
        $obj_type = $this->obj_type();
        if ($obj_type === null) {
            return null;
        }
        $obj = ModelFactory::instance()->get($obj_type, [
            'logger' => $this->logger()
        ]);
        return $obj;
    }

}
