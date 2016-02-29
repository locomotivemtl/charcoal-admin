<?php

namespace Charcoal\Admin\Ui;

use \Exception;
use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\Collection;
use \Charcoal\Model\ModelFactory;

use \Charcoal\Property\PropertyFactory;
use \Charcoal\Admin\Property\PropertyDisplayFactory;

/**
* Fully implements CollectionContainerInterface
*/
trait CollectionContainerTrait
{
    /**
     * @var ModelFactory $modelFactory
     */
    private $modelFactory;

    /**
     * @var CollectionLoader $collectionLoader
     */
    private $collectionLoader;

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


    private $propertyDisplayFactory;

    protected $currentObjId;

    private $proto;

    /**
     * In memory copy of the PropertyDisplay object
     * @var PropertyInputInterface $display
     */
    private $display;

    /**
     * @param ModelFactory $factory
     * @return CollectionContainerInterface Chainable
     */
    public function setModelFactory(ModelFactory $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * Safe Model Factory getter.
     * Create the factory if it was not set / injected.
     *
     * @return ModelFactory
     */
    protected function modelFactory()
    {
        if ($this->modelFactory === null) {
            $this->modelFactory = new ModelFactory();
        }
        return $this->modelFactory;
    }

    /**
     * @param CollectionLoader $loader
     * @return CollectionContainerInterface Chainable
     */
    public function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;
        return $this;
    }

    /**
     * Safe Collection Loader getter.
     * Create the loader if it was not set / injected.
     *
     * @return CollectionLoader
     */
    protected function collectionLoader()
    {
        if ($this->collectionLoader === null) {
            $this->collectionLoader = new CollectionLoader([
                'logger' => $this->logger
            ]);
        }
        return $this->collectionLoader;
    }

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
    protected function createCollectionConfig($data = null)
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
        $obj = $this->modelFactory()->create($objType, [
            'logger' => $this->logger
        ]);

        $loader = $this->collectionLoader();
        $loader->setModel($obj);
        $collectionConfig = $this->collectionConfig();
        if (is_array($collectionConfig) && !empty($collectionConfig)) {
            $loader->setData($collectionConfig);
        }

        $loader->setPagination([
            'page'       => $this->page(),
            'numPerPage' => $this->numPerPage()
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
        $sortedProperties = $this->properties();

        // Collection objects
        $objects = $this->objects();

        // Go through each object to generate an array of properties listed in object's list metadata
        foreach ($objects as $object) {
            $objectProperties = [];

            foreach ($sortedProperties as $propertyIdent => $propertyData) {
                $property = $object->property($propertyIdent);
                $meta = $property->metadata();

                $displayType = $property->displayType();

                $this->display = $this->propertyDisplayFactory()->create($displayType, [
                    'logger' => $this->logger
                ]);
                $this->display->setProperty($property);

                $this->display->setData($meta);
                $this->display->setData($property->viewOptions($displayType));

                $listViewOptions = $this->viewOptions($property->ident());
                if (isset($listViewOptions[$displayType])) {
                    $this->display->setData($listViewOptions[$displayType]);
                }

                $container = \Charcoal\App\App::instance()->getContainer();
                $propertyValue = $container['view']->renderTemplate($displayType, $this->display);


                $objectProperties[] = [
                    'ident' => $propertyIdent,
                    'val'   => $propertyValue
                ];
            };

            $row = [
                'objectId' => $object->id(),
                'objectProperties' => $objectProperties
            ];

            $this->currentObjId = $object->id();
            yield $row;
        }
    }


    private function propertyDisplayFactory()
    {
        if ($this->propertyDisplayFactory === null) {
            $this->propertyDisplayFactory = new PropertyDisplayFactory();
        }
        return $this->propertyDisplayFactory;
    }


    /**
     * @return boolean
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
     * @return object
     */
    public function proto($reload = false)
    {
        if ($this->proto === null || $reload) {
            $objType = $this->objType();
            if ($objType === null) {
                throw new InvalidArgumentException(
                    sprintf('%s Can not create an object prototype: object type is null.', get_class($this))
                );
            }
            $this->proto = $this->modelFactory()->create($objType, [
                'logger' => $this->logger
            ]);
        }
        return $this->proto;
    }
}
