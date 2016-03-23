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

    /**
     * @var PropertyDisplayFactory $propertyDisplayFactory
     */
    private $propertyDisplayFactory;

    /**
     * @var mixed $currentObjId
     */
    protected $currentObjId;

    /**
     * @var ModelInterface $proto
     */
    private $proto;

    /**
     * In memory copy of the PropertyDisplay object
     * @var PropertyInputInterface $display
     */
    private $display;

    /**
     * @param ModelFactory $factory The model factory, to create model objects.
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
     * @param CollectionLoader $loader The collection loader.
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
                'logger' => $this->logger,
                'factory' => $this->modelFactory()
            ]);
        }
        return $this->collectionLoader;
    }

    /**
     * @param string $objType The collection's object type.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
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
     * @param string $collectionIdent The collection identifier.
     * @throws InvalidArgumentException If the ident argument is not a string.
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
     * @param mixed $collectionConfig The collection configuration.
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
     * Stub: reimplement in classes using this trait.
     *
     * @return mixed
     */
    protected function createCollectionConfig()
    {
        return [];
    }

    /**
     * @param integer $page The page number, of the items to load.
     * @throws InvalidArgumentException If the argument is not a number or lower than 0.
     * @return CollectionContainerInterface Chainable
     */
    public function setPage($page)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'Page must be an integer value.'
            );
        }
        if ($page < 1) {
            throw new InvalidArgumentException(
                'Page must be 1 or greater.'
            );
        }
        $this->page = (int)$page;
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
     * @param integer $numPerPage The number of items per page to load.
     * @throws InvalidArgumentException If the argument is not a number or lower than 0.
     * @return CollectionContainerInterface Chainable
     */
    public function setNumPerPage($numPerPage)
    {
        if (!is_numeric($numPerPage)) {
            throw new InvalidArgumentException(
                'Num per page must be an integer value.'
            );
        }
        if ($numPerPage < 1) {
            throw new InvalidArgumentException(
                'Num per page must be 1 or greater.'
            );
        }
        $this->numPerPage = (int)$numPerPage;
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
     * @param mixed $collection The collection.
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
     * @param array $data Optional collection data.
     * @throws Exception If the object type of the colletion has not been set.
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
        $obj = $this->modelFactory()->create($objType);

        $loader = $this->collectionLoader();
        $loader->setModel($obj);
        $collectionConfig = $this->collectionConfig();
        if (is_array($collectionConfig) && !empty($collectionConfig)) {
            unset($collectionConfig['properties']);
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
     * Supplies properties for objects in table template specific to object configuration.
     *
     * @return  void This metod is a generator.
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

    /**
     * @return PropertyDisplayFactory
     */
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
     * @param boolean $reload If true, reload will be forced.
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
