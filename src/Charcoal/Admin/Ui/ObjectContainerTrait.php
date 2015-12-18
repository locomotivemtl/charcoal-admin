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
    * @var string $_obj_base_class
    */
    protected $_obj_base_class;

    /**
    * @var Object $_obj
    */
    protected $_obj;

    /**
    * @param array $data
    * @throws InvalidArgumentException if provided argument is not of type 'array'.
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException(
                'Data must be an array'
            );
        }

        if (isset($data['obj_type']) && $data['obj_type'] !== null) {
            $this->set_obj_type($data['obj_type']);
        }
        if (isset($data['obj_id']) && $data['obj_id'] !== null) {
            $this->set_obj_id($data['obj_id']);
        }
        if (isset($data['obj_base_class']) && $data['obj_base_class'] !== null) {
            $this->set_obj_id($data['obj_base_class']);
        }

        return $this;
    }

    /**
    * @param string $obj_type
    * @throws InvalidArgumentException if provided argument is not of type 'string'.
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_type($obj_type)
    {
        if (!is_string($obj_type)) {
            throw new InvalidArgumentException(
                'Obj type needs to be a string'
            );
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
    * @param string|numeric $obj_id
    * @throws InvalidArgumentException if provided argument is not of type 'scalar'.
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_id($obj_id)
    {
        if (!is_scalar($obj_id)) {
            throw new InvalidArgumentException(
                'Obj ID must be a string or numerical value.'
            );
        }
        $this->_obj_id = $obj_id;
        return $this;
    }

    /**
    * Assign the Object ID
    *
    * @return string|numeric
    */
    public function obj_id()
    {
        return $this->_obj_id;
    }

    /**
    * @param string $obj_base_class
    * @throws InvalidArgumentException if provided argument is not of type 'string'.
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_base_class($obj_base_class)
    {
        if (!is_string($obj_base_class)) {
            throw new InvalidArgumentException(
                'Base class must be a string.'
            );
        }
        $this->_obj_base_class = $obj_base_class;
        return $this;
    }

    /**
    * @return string|null
    */
    public function obj_base_class()
    {
        return $this->_obj_base_class;
    }

    /**
    * Create or load the object.
    *
    *
    * @return ModelInterface
    */
    public function obj()
    {
        if ($this->_obj === null) {
            $this->_obj = $this->create_obj();
        }
        if ($this->obj_id()) {
            $this->_obj = $this->load_obj();
        }
        else if (isset($_GET['clone_id'])) {
            try {
                $obj_class = get_class($this->_obj);
                $clone = new $obj_class([
                    'logger' => $this->logger()
                ]);
                $clone->load($_GET['clone_id']);
                $clone_data =
                $this->_obj->set_data($clone->data());
            } catch (Exception $e) {
                $this->logger()->error('Clone error: '.$e->getMessage());
            }
        }
        else if (isset($_GET['blueprint_id'])) {
            try {
                $model_factory = new ModelFactory();
                $blueprint = $model_factory->create($this->_obj->blueprint_type(), [
                    'logger'=>$this->logger()
                ]);
                $blueprint->load($_GET['blueprint_id']);
                $data = $blueprint->data();
                unset($data[$blueprint->key()]);
                $this->_obj->set_data($blueprint->data());
                // Todo: Blueprint feedback.
            } catch (Exception $e) {
                $this->logger()->error('Blueprint error: '.$e->getMessage());
                // Todo: Error feedback
            }
        }

        return $this->_obj;
    }

    /**
    * @throws Exception
    * @return ModelInterface
    */
    public function create_obj()
    {
        if (!$this->validate_obj_type()) {
            throw new Exception(
                sprintf('Can not create object, Invalid object type. Object type is : %1', $this->obj_type())
            );
        }

        $obj_type = $this->obj_type();

        $factory = new ModelFactory();
        $obj = $factory->create($obj_type, [
            'logger'=>\Charcoal\Charcoal::logger()
        ]);

        return $obj;
    }

    /**
    * @throws Exception
    * @return ModelInterface The loaded object
    */
    public function load_obj()
    {
        if ($this->_obj === null) {
            $this->_obj = $this->create_obj();
        }
        $obj = $this->_obj;

        $obj_id = $this->obj_id();
        if (!$obj_id) {
            throw new Exception(
                'Can not load object. Object ID is not defined.'
            );
        }
        $obj->load($obj_id);
        return $obj;
    }

    /**
    * @throws Exception
    * @return boolean
    */
    protected function validate_obj_type()
    {
        try {
            $obj_type = $this->obj_type();
            if (!$obj_type) {
                return false;
            }
            $factory = new ModelFactory();
            // Catch exception to know if the obj_type is valid
            $obj = $factory->get($obj_type, [
                'logger'=>\Charcoal\Charcoal::logger()
            ]);
            if (!$this->validate_obj_base_class($obj)) {
                throw Exception(
                    'Can not create object, type is not an instance of obj_base_class'
                );
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
    * @return boolean
    */
    protected function validate_obj_base_class($obj)
    {
        $obj_base_class = $this->obj_base_class();
        if (!$obj_base_class) {
            // If no base class is set, then no validation is performed.
            return true;
        }
        try {
            return ($obj instanceof $obj_base_class);
        } catch (Exception $e) {
            return false;
        }
    }
}
