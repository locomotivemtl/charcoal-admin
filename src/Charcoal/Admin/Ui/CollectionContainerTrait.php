<?php

namespace Charcoal\Admin\Ui;

use \InvalidArgumentException as InvalidArgumentException;

trait CollectionContainerTrait
{
    protected $_obj_type;
    protected $_collection_ident;
    protected $_collection_config;

    /**
    * @param array $data
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
    public function set_collection_config($dashboard_config)
    {
        $this->_collection_config = $collection_config;
        return $this;
    }

    /**
    * @return mixed
    */
    public function collection_config()
    {
        if ($this->collection_config === null) {
            //$this->_collection_config = $this->create_collection_config();
        }
        return $this->_collection_config;
    }

    /**
    * @param array $data
    * @return mixed
    */
    //abstract public function create_collection_config($data = null);


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
            // $this->_collection = $this->create_collection();
        }
        return $this->_collection;
    }

    //abstract public function create_collection($data = null);


    /**
    * @return array
    */
    public function objects()
    {
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
        return new $obj_type;
    }

}
