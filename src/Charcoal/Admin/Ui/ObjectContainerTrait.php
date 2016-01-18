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
    * @var string $objType
    */
    protected $objType;
    /**
    * @var string $objId
    */
    protected $objId;

    /**
    * @var string $objBaseClass
    */
    protected $objBaseClass;

    /**
    * @var Object $obj
    */
    protected $obj;

    /**
    * @param string $objType
    * @throws InvalidArgumentException if provided argument is not of type 'string'.
    * @return ObjectContainerInterface Chainable
    */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Obj type needs to be a string'
            );
        }
        $this->objType = str_replace(['.', '_'], '/', $objType);
        return $this;
    }

    /**
    * @return string
    */
    public function objType()
    {
        return $this->objType;
    }

    /**
    * @param string|numeric $objId
    * @throws InvalidArgumentException if provided argument is not of type 'scalar'.
    * @return ObjectContainerInterface Chainable
    */
    public function setObjId($objId)
    {
        if (!is_scalar($objId)) {
            throw new InvalidArgumentException(
                'Obj ID must be a string or numerical value.'
            );
        }
        $this->objId = $objId;
        return $this;
    }

    /**
    * Assign the Object ID
    *
    * @return string|numeric
    */
    public function objId()
    {
        return $this->objId;
    }

    /**
    * @param string $objBaseClass
    * @throws InvalidArgumentException if provided argument is not of type 'string'.
    * @return ObjectContainerInterface Chainable
    */
    public function setObjBaseClass($objBaseClass)
    {
        if (!is_string($objBaseClass)) {
            throw new InvalidArgumentException(
                'Base class must be a string.'
            );
        }
        $this->objBaseClass = $objBaseClass;
        return $this;
    }

    /**
    * @return string|null
    */
    public function objBaseClass()
    {
        return $this->objBaseClass;
    }

    /**
    * Create or load the object.
    *
    *
    * @return ModelInterface
    */
    public function obj()
    {
        if ($this->obj === null) {
            $this->obj = $this->createObj();
        }

        if ($this->objId()) {
            $this->obj = $this->loadObj();
        } else if (isset($GET['clone_id'])) {
            try {
                $objClass = getClass($this->obj);
                $clone = new $objClass([
                    'logger' => $this->logger()
                ]);
                $clone->load($GET['clone_id']);
                $clone_data =
                $this->obj->set_data($clone->data());
            } catch (Exception $e) {
                $this->logger()->error('Clone error: '.$e->getMessage());
            }
        } else if (isset($GET['blueprint_id'])) {
            try {
                $model_factory = new ModelFactory();
                $blueprint = $model_factory->create($this->obj->blueprintType(), [
                    'logger'=>$this->logger()
                ]);
                $blueprint->load($GET['blueprint_id']);
                $data = $blueprint->data();
                unset($data[$blueprint->key()]);
                $this->obj->set_data($blueprint->data());
                // Todo: Blueprint feedback.
            } catch (Exception $e) {
                $this->logger()->error('Blueprint error: '.$e->getMessage());
                // Todo: Error feedback
            }
        }

        return $this->obj;
    }

    /**
    * @throws Exception
    * @return ModelInterface
    */
    public function createObj()
    {
        if (!$this->validateObjType()) {
            throw new Exception(
                sprintf('Can not create object, Invalid object type. Object type is : %1', $this->objType())
            );
        }

        $objType = $this->objType();

        $factory = new ModelFactory();
        $obj = $factory->create($objType, [
            'logger'=>$this->logger
        ]);

        return $obj;
    }

    /**
    * @throws Exception
    * @return ModelInterface The loaded object
    */
    public function loadObj()
    {
        if ($this->obj === null) {
            $this->obj = $this->createObj();
        }
        $obj = $this->obj;

        $objId = $this->objId();
        if (!$objId) {
            throw new Exception(
                'Can not load object. Object ID is not defined.'
            );
        }
        $obj->load($objId);
        return $obj;
    }

    /**
    * @throws Exception
    * @return boolean
    */
    protected function validateObjType()
    {
        try {
            $objType = $this->objType();
            $factory = new ModelFactory();
            // Catch exception to know if the objType is valid

            $obj = $factory->get($objType, [
                'logger'=>$this->logger
            ]);
            if (!$this->validateObjBaseClass($obj)) {
                throw Exception(
                    'Can not create object, type is not an instance of objBaseClass'
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
    protected function validateObjBaseClass($obj)
    {
        $objBaseClass = $this->objBaseClass();
        if (!$objBaseClass) {
            // If no base class is set, then no validation is performed.
            return true;
        }
        try {
            return ($obj instanceof $objBaseClass);
        } catch (Exception $e) {
            return false;
        }
    }
}
