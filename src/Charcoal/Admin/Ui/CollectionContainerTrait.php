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
        if (isset($data['collection_ident']) && $data['collection_ident'] !== null) {
            $this->set_collection_ident($data['collection_ident']);
        }
        if (isset($data['collection_config']) && $data['collection_config'] !== null) {
            $this->set_collection_config($data['collection_config']);
        }

        return $this;
    }

    /**
    * @param string $obj_type
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_obj_type($obj_type)
    {
        if (!is_string($obj_type)) {
            throw new InvalidArgumentException('Obj type must be a string');
        }
        $this->_obj_type = $obj_type;
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
    * @param string $collection_ident
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_ident($collection_ident)
    {
        if (!is_string($collection_ident)) {
            throw new InvalidArgumentException('Collection ident must be a string');
        }
        $this->_collection_ident = $collection_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function collection_ident()
    {
        return $this->_collection_ident;
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
    * @param array $data
    * @throws Exception
    * @return CollectionLoader
    */
    public function create_collection($data = null)
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
        //var_dump($this->collection()->objects());
        return $this->collection()->objects();
    }

    /**
    * @return boolean
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
