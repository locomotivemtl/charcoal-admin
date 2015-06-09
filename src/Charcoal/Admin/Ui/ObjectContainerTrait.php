<?php

namespace Charcoal\Admin\Ui;

use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Model\ModelFactory as ModelFactory;

/**
* Fully implements ObjectContainerInterface
*/
trait ObjectContainerTrait
{
    /**
    * @var string $_obj_type
    */
    protected $_obj_type;
    /**
    * @var string $_obj_id
    */
    protected $_obj_id;
    /**
    * @var Object $_obj
    */
    protected $_obj;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        if (isset($data['obj_type']) && $data['obj_type'] !== null) {
            $this->set_obj_type($data['obj_type']);
        }
        if (isset($data['obj_id']) && $data['obj_id'] !== null) {
            $this->set_obj_id($data['obj_id']);
        }

        return $this;
    }

    /**
    * @param string $obj_type
    * @throws InvalidArgumentException
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_type($obj_type)
    {
        if (!is_string($obj_type)) {
            throw new InvalidArgumentException('Obj type needs to be a string');
        }
        $this->_obj_type = $obj_type;
        return $this;
    }

    /**
    * @return string
    */
    public function obj_type()
    {
        return str_replace(['.', '_'], '/', $this->_obj_type);
    }

    /**
    * @param mixed $obj_id
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_id($obj_id)
    {
        $this->_obj_id = $obj_id;
        return $this;
    }

    /**
    * @return mixed
    */
    public function obj_id()
    {
        return $this->_obj_id;
    }

    /**
    * @return Object
    */
    public function obj()
    {
        if ($this->_obj === null) {
            $this->_obj = $this->create_obj();
        }
        return $this->_obj;
    }

    /**
    * @throws Exception
    * @return Object
    */
   public function create_obj()
   {
        $obj_type = $this->_obj_type;
        if (!$obj_type) {
            throw new Exception('Can not create object, type is not defined.');
        }

        $obj = ModelFactory::instance()->get($obj_type);

        $obj_id = $this->obj_id();
        if ($obj_id) {
            $obj->load($obj_id);
        }
        return $obj;
   }
}
