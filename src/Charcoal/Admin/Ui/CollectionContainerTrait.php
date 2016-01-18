<?php

namespace Charcoal\Admin\Ui;

use \Exception as Exception;
use \InvalidArgumentException;

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
    * @var string $objType
    */
    private $objType;

    /**
    * @var string $collectionIdent
    */
    private $collectionIdent;

    /**
    * @var mixed $collectionConfig
    */
    private $collectionConfig;

    /**
    * @var integer $page
    */
    private $page = 1;

    /**
    * @var integer $numPerPage
    */
    private $numPerPage = 50;


    /**
    * @var Collection $collection
    */
    private $collection;

    private $modelFactory;

    /**
    * @param string $objType
    * @throws InvalidArgumentException if provided argument is not of type 'string'.
    * @return CollectionContainerInterface Chainable
    */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Obj type must be a string'
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
    * @param string $collectionIdent
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function setCollectionIdent($collectionIdent)
    {
        if (!is_string($collectionIdent)) {
            throw new InvalidArgumentException(
                'Collection identifier must be a string'
            );
        }
        $this->collectionIdent = $collectionIdent;
        return $this;
    }

    /**
    * @return string|null
    */
    public function collectionIdent()
    {
        return $this->collectionIdent;
    }

    /**
    * @param mixed $collectionConfig
    * @return CollectionContainerInterface Chainable
    */
    public function setCollectionConfig($collectionConfig)
    {
        $this->collectionConfig = $collectionConfig;
        return $this;
    }

    /**
    * @return mixed
    */
    public function collectionConfig()
    {
        if ($this->collectionConfig === null) {
            $this->collectionConfig = $this->createCollectionConfig();
        }
        return $this->collectionConfig;
    }

    /**
    * @param array $data
    * @return mixed
    */
    public function createCollectionConfig($data = null)
    {
        unset($data);
        return [];
    }

    /**
    * @param integer $page
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function setPage($page)
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
    * @param integer $numPerPage
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function setNumPerPage($numPerPage)
    {
        if (!is_int($numPerPage)) {
            throw new InvalidArgumentException(
                'Num per page must be an integer value.'
            );
        }
        if ($numPerPage < 1) {
            throw new InvalidArgumentException(
                'Num per page must be 1 or greater.'
            );
        }
        $this->numPerPage = $numPerPage;
        return $this;
    }

    /**
    * @return integer
    */
    public function numPerPage()
    {
        return $this->numPerPage;
    }

    /**
    * @param mixed $collection
    * @return CollectionContainerInterface Chainable
    */
    public function setCollection($collection)
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
            $this->collection = $this->createCollection();
        }
        return $this->collection;
    }

    /**
    * @todo Integrate $data; merge with $collectionConfig
    * @param array $data Optional
    * @throws Exception
    * @return CollectionLoader
    */
    public function createCollection(array $data = null)
    {
        unset($data);
        $objType = $this->objType();
        if (!$objType) {
            throw new Exception(
                __CLASS__.'::'.__FUNCTION__.' - Can not create collection, object type is not defined.'
            );
        }
        $factory = $this->model_factory();
        $obj = $factory->create($objType, [
            'logger' => $this->logger
        ]);

        $loader = new CollectionLoader([
            'logger' => $this->logger
        ]);
        $loader->set_model($obj);
        $collectionConfig = $this->collectionConfig();
        if (is_array($collectionConfig) && !empty($collectionConfig)) {
            $loader->set_data($collectionConfig);
        }

        $loader->setPagination([
            'page'          => $this->page(),
            'numPerPage'    => $this->numPerPage()
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
    public function objectRows()
    {
        // Get properties as defined in object's list metadata
        $sorted_properties = $this->properties();

        // Collection objects
        $objects = $this->objects();

        // Go through each object to generate an array of properties listed in object's list metadata
        foreach ($objects as $object) {
            $object_properties = [];

            foreach ($sorted_properties as $propertyIdent => $property_data) {
                $property = $object->property($propertyIdent);
                $property_value = $property->display_val();

                $object_properties[] = [
                    'ident' => $propertyIdent,
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
    public function hasObjects()
    {
        return (count($this->objects()) > 0);
    }

    /**
    * @return integer
    */
    public function numObjects()
    {
        return count($this->objects());
    }

    /**
    * @throws InvalidArgumentException If the object type is not defined / can not create prototype.
    * @return Object
    */
    public function proto()
    {
        $objType = $this->objType();
        if ($objType === null) {
            throw new InvalidArgumentException(
                sprintf('%s Can not create an object prototype: object type is null.', get_class($this))
            );
        }
        $factory = $this->modelFactory();
        $obj = $factory->get($objType, [
            'logger' => $this->logger
        ]);
        return $obj;
    }

    private function modelFactory()
    {
        if ($this->modelFactory === null) {
            $this->modelFactory = new ModelFactory();
        }
        return $this->modelFactory;
    }

}
