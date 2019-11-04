<?php

namespace Charcoal\Admin\Ui;

use Exception;
use InvalidArgumentException;
use UnexpectedValueException;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\Collection;
use Charcoal\Model\ModelInterface;
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\Pagination;
use Charcoal\Source\PaginationInterface;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewInterface;

/**
 * Fully implements CollectionContainerInterface
 */
trait CollectionContainerTrait
{
    /**
     * @var FactoryInterface $modelFactory
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
     * Collection configuration.
     *
     * @var array|null
     */
    private $collectionConfig;

    /**
     * Default collection configuration.
     *
     * @var array|null
     */
    private $defaultCollectionConfig;

    /**
     * Object labels.
     *
     * @var array|null
     */
    private $objLabels;

    /**
     * @var integer $numTotal
     */
    private $numTotal;

    /**
     * The collection's prepared filters.
     *
     * @var FilterInterface[]
     */
    protected $filters;

    /**
     * The collection's prepared orders.
     *
     * @var OrderInterface[]
     */
    protected $orders;

    /**
     * The collection's prepared pagiantion.
     *
     * @var PaginationInterface
     */
    protected $pagination;

    /**
     * @var Collection $collection
     */
    private $collection;

    /**
     * @var FactoryInterface $propertyDisplayFactory
     */
    private $propertyDisplayFactory;

    /**
     * @var mixed $currentObjId
     */
    protected $currentObjId;

    /**
     * @var mixed $currentObj
     */
    protected $currentObj;

    /**
     * @var ModelInterface $proto
     */
    private $proto;

    /**
     * In memory copy of the PropertyDisplay object
     * @var PropertyInputInterface $display
     */
    protected $display;

    /**
     * @var ViewInterface $view
     */
    private $view;

    /**
     * Holds a list of all renderable classes.
     *
     * Format: `class => boolean`
     *
     * @var boolean[]
     */
    protected static $objRenderableCache = [];

    /**
     * @param ViewInterface|array $view The view instance.
     * @return CollectionContainerInterface Chainable
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @throws Exception If the view instance is not previously set / injected.
     * @return ViewInterface The object's view.
     */
    public function view()
    {
        if ($this->view === null) {
            throw new Exception(
                'View instance is not set for table widget'
            );
        }

        return $this->view;
    }

    /**
     * @param FactoryInterface $factory The model factory, to create model objects.
     * @return CollectionContainerInterface Chainable
     */
    public function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;

        return $this;
    }

    /**
     * Model Factory getter.
     *
     * @throws Exception If the model factory was not previously set.
     * @return FactoryInterface
     */
    protected function modelFactory()
    {
        if ($this->modelFactory === null) {
            throw new Exception(sprintf(
                'Model Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->modelFactory;
    }

    /**
     * @param FactoryInterface $factory The property display factory.
     * @return CollectionContainerInterface Chainable
     */
    private function setPropertyDisplayFactory(FactoryInterface $factory)
    {
        $this->propertyDisplayFactory = $factory;

        return $this;
    }

    /**
     * @throws Exception If the property display factory was not previously injected / set.
     * @return FactoryInterface
     */
    private function propertyDisplayFactory()
    {
        if ($this->propertyDisplayFactory === null) {
            throw new Exception(sprintf(
                'Property display factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->propertyDisplayFactory;
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
            $this->collectionLoader = $this->createCollectionLoader();
        }

        return $this->collectionLoader;
    }

    /**
     * Create a collection loader.
     *
     * @return CollectionLoader
     */
    protected function createCollectionLoader()
    {
        return new CollectionLoader([
            'logger'  => $this->logger,
            'factory' => $this->modelFactory(),
        ]);
    }

    /**
     * Configure the collection loader.
     *
     * This method ensures the object type exists before altering the instance.
     *
     * @param  CollectionLoader $loader The collection loader to prepare.
     * @param  array|null       $data   Optional collection data.
     * @return void
     */
    protected function configureCollectionLoader(CollectionLoader $loader, array $data = null)
    {
        $objType = $this->getObjTypeOrFail();

        $loader->setModel($this->proto());

        $config = $this->collectionConfig();
        if (is_array($config) && !empty($config)) {
            unset($config['properties']);
            $loader->setData($config);
        }

        if ($data) {
            $loader->setData($data);
        }

        $loader->isConfigured = true;
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
        $this->objType = str_replace([ '.', '_' ], '/', $objType);

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
     * Retrieve the current object type or throw an exception.
     *
     * @throws UnexpectedValueException If the collection object type is invalid or missing.
     * @return string
     */
    public function getObjTypeOrFail()
    {
        $objType = $this->objType();

        if (!$objType) {
            throw new UnexpectedValueException(sprintf(
                '%1$s cannot create collection. Object type is not defined.',
                get_class($this)
            ));
        }

        return $objType;
    }

    /**
     * Set the key for the collection structure to use.
     *
     * @param  string $collectionIdent The collection identifier.
     * @throws InvalidArgumentException If the identifier argument is not a string.
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
     * Retrieve a key for the collection structure to use.
     *
     * If the collection key is undefined, resolve a fallback.
     *
     * @return string
     */
    public function collectionIdentFallback()
    {
        $metadata = $this->proto()->metadata();

        if (isset($metadata['admin']['defaultList'])) {
            return $metadata['admin']['defaultList'];
        } elseif (isset($metadata['admin']['default_list'])) {
            return $metadata['admin']['default_list'];
        }

        return $this->collectionIdent;
    }

    /**
     * Retrieve the key for the collection structure to use.
     *
     * @return string|null
     */
    public function collectionIdent()
    {
        return $this->collectionIdent;
    }

    /**
     * Return the current collection metadata.
     *
     * @return array
     */
    public function collectionMetadata()
    {
        $proto = $this->proto();
        $collectionIdent = $this->collectionIdent();

        if (!$collectionIdent) {
            $collectionIdent = $this->collectionIdentFallback();
        }

        if ($collectionIdent && $this->isObjRenderable($proto)) {
            $collectionIdent = $proto->render($collectionIdent);
        }

        if (!$collectionIdent) {
            return [];
        }

        $objMeta = $proto->metadata();

        if (isset($objMeta['admin']['lists'][$collectionIdent])) {
            return $objMeta['admin']['lists'][$collectionIdent];
        } else {
            return [];
        }
    }

    /**
     * Retrieve the collection configset.
     *
     * @return array|null
     */
    public function collectionConfig()
    {
        if ($this->collectionConfig === null) {
            $this->collectionConfig = $this->createCollectionConfig();
        }

        return $this->collectionConfig;
    }

    /**
     * Replace the collection's configset with the given parameters.
     *
     * @param  mixed $config New collection config values.
     * @return CollectionContainerInterface Chainable
     */
    public function setCollectionConfig($config)
    {
        if (empty($config) || !is_array($config)) {
            $config = [];
        }

        $this->collectionConfig = array_replace_recursive(
            $this->defaultCollectionConfig(),
            $this->parseCollectionConfig($config)
        );

        return $this;
    }

    /**
     * Merge given parameters into the collection's configset.
     *
     * @param  array $config New collection config values.
     * @return self
     */
    public function mergeCollectionConfig(array $config)
    {
        if ($this->collectionConfig === null) {
            $this->setCollectionConfig($config);

            return $this;
        }

        $this->collectionConfig = array_replace_recursive(
            $this->defaultCollectionConfig(),
            $this->collectionConfig,
            $this->parseCollectionConfig($config)
        );

        return $this;
    }

    /**
     * Stub: Parse given parameters into the collection's config set.
     *
     * @param  array $config New collection config values.
     * @return array
     */
    protected function parseCollectionConfig(array $config)
    {
        return array_filter($config, function ($val) {
            return !empty($val) || is_numeric($val);
        });
    }

    /**
     * Retrieve the default collection configuration.
     *
     * The default configset is determined by the collection ident and object type, if assigned.
     *
     * @return array|null
     */
    protected function defaultCollectionConfig()
    {
        if ($this->defaultCollectionConfig === null) {
            $this->defaultCollectionConfig = $this->collectionMetadata();
        }

        return $this->defaultCollectionConfig;
    }

    /**
     * Stub: reimplement in classes using this trait.
     *
     * @return mixed
     */
    protected function createCollectionConfig()
    {
        return $this->collectionMetadata();
    }

    /**
     * @return boolean
     */
    public function hasPagination()
    {
        return ($this->pagination() instanceof Pagination);
    }

    /**
     * @return PaginationInterface
     */
    public function pagination()
    {
        if ($this->pagination === null || !$this->pagination instanceof PaginationInterface) {
            $this->pagination = $this->createPagination();
            $collectionConfig = $this->collectionConfig();
            if (isset($collectionConfig['pagination'])) {
                $this->pagination->setData($collectionConfig['pagination']);
            }
        }

        return $this->pagination;
    }

    /**
     * @return PaginationInterface
     */
    protected function createPagination()
    {
        $pagination = new Pagination();
        return $pagination;
    }

    /**
     * @return integer
     */
    public function page()
    {
        return $this->pagination()->page();
    }

    /**
     * @return integer
     */
    public function numPerPage()
    {
        return $this->pagination()->numPerPage();
    }

    /**
     * @return integer
     */
    public function numPages()
    {
        if ($this->numPerPage() === 0) {
            return 0;
        }

        return ceil($this->numTotal() / $this->numPerPage());
    }

    /**
     * @return boolean
     */
    public function hasFilters()
    {
        return count($this->filters()) > 0;
    }

    /**
     * @return FilterInterface[]
     */
    public function filters()
    {
        if ($this->filters === null) {
            $this->filters = [];
            $collectionConfig = $this->collectionConfig();
            if (isset($collectionConfig['filters'])) {
                foreach ($collectionConfig['filters'] as $key => $data) {
                    if ($data instanceof FilterInterface) {
                        $filter = $data;
                    } elseif (is_array($data)) {
                        $filter = $this->createFilter();
                        $filter->setData($data);
                    }
                    $this->filters[$key] = $filter;
                }
            }
        }

        return $this->filters;
    }

    /**
     * @return FilterInterface
     */
    protected function createFilter()
    {
        $filter = new Filter();
        return $filter;
    }

    /**
     * @return boolean
     */
    public function hasOrders()
    {
        return count($this->orders()) > 0;
    }

    /**
     * @return OrderInterface[]
     */
    public function orders()
    {
        if ($this->orders === null) {
            $this->orders = [];
            $collectionConfig = $this->collectionConfig();
            if (isset($collectionConfig['orders'])) {
                foreach ($collectionConfig['orders'] as $key => $data) {
                    if ($data instanceof OrderInterface) {
                        $order = $data;
                    } elseif (is_array($data)) {
                        $order = $this->createOrder();
                        $order->setData($data);
                    }
                    $this->orders[$key] = $order;
                }
            }
        }

        return $this->orders;
    }

    /**
     * @return OrderInterface
     */
    protected function createOrder()
    {
        $order = new Order();
        return $order;
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
     * @return CollectionLoader
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
        $objType = $this->getObjTypeOrFail();

        $loader = $this->collectionLoader();
        $this->configureCollectionLoader($loader, $data);

        $collection = $loader->load();
        return $collection;
    }

    /**
     * @return array
     */
    public function objects()
    {
        return $this->collection()->objects();
    }

    /**
     * Sort the objects before they are displayed as rows.
     *
     * This method is useful for classes using this trait.
     *
     * @return array
     */
    public function sortObjects()
    {
        return $this->objects();
    }

    /**
     * Sort the properties before they are displayed as columns.
     *
     * This method is useful for classes using this trait.
     *
     * @return array
     */
    public function sortProperties()
    {
        return $this->properties();
    }

    /**
     * Supplies properties for objects in table template specific to object configuration.
     *
     * @return array This metod is a generator.
     */
    public function objectRows()
    {
        // Get properties as defined in object's list metadata
        $properties  = $this->sortProperties();
        $propOptions = $this->propertiesOptions();

        // Collection objects
        $objects = $this->sortObjects();

        // Go through each object to generate an array of properties listed in object's list metadata
        foreach ($objects as $object) {
            if (isset($object['requiredAclPermissions']) && !empty($object['requiredAclPermissions'])) {
                if ($this->hasPermissions($object['requiredAclPermissions']) === false) {
                    continue;
                }
            }

            $objectProperties = [];
            foreach ($properties as $propertyIdent => $propertyData) {
                $property = $object->property($propertyIdent);

                if (isset($propOptions[$propertyIdent]) && is_array($propOptions[$propertyIdent])) {
                    $property->setData($propOptions[$propertyIdent]);
                }

                $this->setupDisplayPropertyValue($object, $property);

                $displayType = $this->display->displayType();
                $this->display->setPropertyVal($object->propertyValue($propertyIdent));

                $propertyValue = $this->view()->renderTemplate($displayType, $this->display);

                $cell = $this->parsePropertyCell($object, $property, $propertyValue);
                $objectProperties[] = $cell;
            };

            $this->currentObj = $object;
            $this->currentObjId = $object->id();

            $row = $this->parseObjectRow($object, $objectProperties);

            yield $row;
        }

        $this->currentObj = null;
        $this->currentObjId = null;
    }

    /**
     * Setup the property's display value before its assigned to the object row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  ModelInterface    $object   The current row's object.
     * @param  PropertyInterface $property The current property.
     * @return void
     */
    protected function setupDisplayPropertyValue(ModelInterface $object, PropertyInterface $property)
    {
        $displayType = $property['displayType'];

        $this->display = $this->propertyDisplayFactory()->create($displayType);
        $this->display->setDisplayType($displayType);
        $this->display->setProperty($property);

        $metadata = $property->metadata();
        $objMetadata = $object->metadata()->property($property->ident());
        if ($objMetadata) {
            $metadata->setData($objMetadata);
        }

        $this->display->setData($metadata->data());

        $viewOptions = $property->viewOptions($displayType);
        $this->display->setData($viewOptions);

        $propertyIdent = $property->ident();
        $propertiesOptions = $this->propertiesOptions();
        if (isset($propertiesOptions[$propertyIdent])) {
            $this->display->setData(array_diff_key($propertiesOptions[$propertyIdent], [ 'view_options' => true ]));
        }
    }

    /**
     * Filter the property before its assigned to the object row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  ModelInterface    $object        The current row's object.
     * @param  PropertyInterface $property      The current property.
     * @param  string            $propertyValue The property $key's display value.
     * @return array
     */
    protected function parsePropertyCell(
        ModelInterface $object,
        PropertyInterface $property,
        $propertyValue
    ) {
        unset($object);

        return [
            'ident' => $property->ident(),
            'val'   => trim($propertyValue)
        ];
    }

    /**
     * Filter the object before its assigned to the row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  ModelInterface $object           The current row's object.
     * @param  array          $objectProperties The $object's display properties.
     * @return array
     */
    protected function parseObjectRow(ModelInterface $object, array $objectProperties)
    {
        return [
            'object'           => $object,
            'objectId'         => $object->id(),
            'objectType'       => $object->objType(),
            'objectProperties' => $objectProperties
        ];
    }

    /**
     * @return boolean
     */
    public function hasObjects()
    {
        return ($this->numObjects() > 0);
    }

    /**
     * @return integer
     */
    public function numObjects()
    {
        return count($this->objects());
    }

    /**
     * @throws Exception If obj type was not set.
     * @return integer
     */
    public function numTotal()
    {
        if ($this->numTotal === null) {
            $objType = $this->getObjTypeOrFail();

            $loader = $this->collectionLoader();
            $this->configureCollectionLoader($loader);

            $this->numTotal = $loader->loadCount();
        }

        return $this->numTotal;
    }

    /**
     * Retrieve the object's labelling.
     *
     * @return array
     */

    /**
     * Retrieve the object's labels.
     *
     * @return array|null
     */
    public function objLabels()
    {
        if ($this->objLabels === null) {
            $objLabels = [];
            $proto = $this->proto();
            $objMetadata = $proto->metadata();
            if (isset($objMetadata['labels']) && !empty($objMetadata['labels'])) {
                $objLabels = $objMetadata['labels'];
                array_walk($objLabels, function(&$value) {
                    $value = $this->translator()->translation($value);
                });
                $this->objLabels = $objLabels;
            }
        }

        return $this->objLabels;
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
                throw new InvalidArgumentException(sprintf(
                    '%s Can not create an object prototype: object type is null.',
                    get_class($this)
                ));
            }
            $this->proto = $this->modelFactory()->create($objType);
        }

        return $this->proto;
    }

    /**
     * Retrieve the current object in a collection or its prototype.
     *
     * @return ModelInterface
     */
    protected function getCurrentObjOrProto()
    {
        return $this->currentObj ?: $this->proto();
    }

    /**
     * Determine if the model implements {@see \Charcoal\View\ViewableInterface}.
     *
     * @see \Charcoal\Admin\Ui\ObjectContainerTrait::isObjRenderable()
     *
     * @param  string|object $obj      Object type or instance to test.
     * @param  boolean       $toString Whether to test for `__toString()`.
     * @return boolean
     */
    protected function isObjRenderable($obj, $toString = false)
    {
        if (is_string($obj)) {
            if (!method_exists($this, 'modelFactory')) {
                return false;
            }

            $obj = $this->modelFactory()->get($obj);
        }

        if (!is_object($obj)) {
            return false;
        }

        $key = get_class($obj);

        if (isset(static::$objRenderableCache[$key])) {
            return static::$objRenderableCache[$key];
        }

        $check = false;
        if (is_object($obj)) {
            if (($obj instanceof ViewableInterface) && ($obj->view() instanceof ViewInterface)) {
                $check = true;
            } elseif ($toString && is_callable([ $obj, '__toString()' ])) {
                $check = true;
            }
        }

        static::$objRenderableCache[$key] = $check;

        return static::$objRenderableCache[$key];
    }
}
