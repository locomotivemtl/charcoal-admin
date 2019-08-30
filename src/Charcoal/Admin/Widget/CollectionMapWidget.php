<?php

namespace Charcoal\Admin\Widget;

use ArrayAccess;
use RuntimeException;
use InvalidArgumentException;
use UnexpectedValueException;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Support\HttpAwareTrait;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;

/**
 * Displays a collection of models on a map.
 */
class CollectionMapWidget extends AdminWidget implements CollectionContainerInterface
{
    use CollectionContainerTrait;
    use HttpAwareTrait;

    /**
     * The API key for the mapping service.
     *
     * @var array
     */
    private $apiKey;

    /**
     * @var \Charcoal\Model\AbstractModel[] $mapObjects
     */
    private $mapObjects;

    /**
     * The ident of the object's property for the latitude.
     * @var string $latProperty
     */
    private $latProperty;

    /**
     * The ident of the object's property for the longitude.
     * @var string $latProperty
     */
    private $lonProperty;

    /**
     * @var string $polygonProperty
     */
    private $polygonProperty;

    /**
     * @var string $pathProperty
     */
    private $pathProperty;

    /**
     * @var string $infoboxTemplate
     */
    public $infoboxTemplate = '';
    /**
     * @param array $data The widget data.
     * @return TableWidget Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        $this->mergeDataSources($data);

        return $this;
    }

    /**
     * Sets the API key for the mapping service.
     *
     * @param  string $key An API key.
     * @return self
     */
    public function setApiKey($key)
    {
        $this->apiKey = $key;

        return $this;
    }

    /**
     * Retrieve API key for the mapping service.
     *
     * @return string
     */
    public function apiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $p The latitude property ident.
     * @return MapWidget Chainable
     */
    public function setLatProperty($p)
    {
        $this->latProperty = $p;
        return $this;
    }

    /**
     * @return string
     */
    public function latProperty()
    {
        return $this->latProperty;
    }

    /**
     * @param string $p The longitude property ident.
     * @return MapWidget Chainable
     */
    public function setLonProperty($p)
    {
        $this->lonProperty = $p;
        return $this;
    }

    /**
     * @return string
     */
    public function lonProperty()
    {
        return $this->lonProperty;
    }

    /**
     * @param string $p The polygon property ident.
     * @return MapWidget Chainable
     */
    public function setPolygonProperty($p)
    {
        $this->polygonProperty = $p;
        return $this;
    }

    /**
     * @return string
     */
    public function polygonProperty()
    {
        return $this->polygonProperty;
    }

    /**
     * @param string $p The path property ident.
     * @return MapWidget Chainable
     */
    public function setPathProperty($p)
    {
        $this->pathProperty = $p;
        return $this;
    }

    /**
     * @return string
     */
    public function pathProperty()
    {
        return $this->pathProperty;
    }

    /**
     * @param string $template The infobox template ident.
     * @return CollectionMapWidget Chainable
     */
    public function setInfoboxTemplate($template)
    {
        $this->infoboxTemplate = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function infoboxTemplate()
    {
        return $this->infoboxTemplate;
    }

    /**
     * Return all the objs with geographical information
     *
     * @throws UnexpectedValueException If the object type of the colletion is missing.
     * @return Collection
     */
    public function mapObjects()
    {
        if ($this->mapObjects === null) {
            $objType = $this->objType();
            if (!$objType) {
                throw new UnexpectedValueException(sprintf(
                    '%1$s cannot create collection map. Object type is not defined.',
                    get_class($this)
                ));
            }

            $loader = $this->collectionLoader();
            $loader->setModel($this->proto());

            $collectionConfig = $this->collectionConfig();
            if (is_array($collectionConfig) && !empty($collectionConfig)) {
                unset($collectionConfig['properties']);
                $loader->setData($collectionConfig);
            }

            $callback = function(&$obj) {
                $obj->mapInfoboxTemplate = $this->infoboxTemplate();

                if ($this->latProperty() && $this->latProperty()) {
                    $obj->mapShowMarker = true;
                    $obj->mapLat = $this->getPropertyValue($obj, $this->latProperty());
                    $obj->mapLon = $this->getPropertyValue($obj, $this->lonProperty());

                    if (!$obj->mapLat || !$obj->mapLon) {
                        $obj = null;
                    }
                } else {
                    $obj->mapShowMarker = false;
                }

                if ($this->pathProperty()) {
                    $mapPath = $this->getPropertyValue($obj, $this->pathProperty());
                    if ($mapPath) {
                        $obj->mapShowPath = true;
                        // Same type of coords.
                        $obj->mapPath = $this->formatPolygon($mapPath);

                        if (!$obj->mapPath) {
                            $obj = null;
                        }
                    } else {
                        $obj->mapShowPath = false;
                    }
                }

                if ($this->polygonProperty()) {
                    $mapPolygon = $this->getPropertyValue($obj, $this->polygonProperty());
                    if ($mapPolygon) {
                        $obj->mapShowPolygon = true;
                        $obj->mapPolygon = $this->formatPolygon($mapPolygon);

                        if (!$obj->mapPolygon) {
                            $obj = null;
                        }
                    } else {
                        $obj->mapShowPolygon = false;
                    }
                }
            };

            $loader->setCallback($callback->bindTo($this));

            $this->mapObjects = $loader->load();
        }

        foreach ($this->mapObjects as $obj) {
            $this->setDynamicTemplate('widget_template', $obj->mapInfoboxTemplate);
            yield $obj;
        }
    }

    /**
     * @return boolean
     */
    public function showInfobox()
    {
        return ($this->infoboxTemplate != '');
    }


    /**
     * Fetch metadata from the current request.
     *
     * @return array
     */
    public function dataFromRequest()
    {
        return $this->httpRequest()->getParams($this->acceptedRequestData());
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    public function acceptedRequestData()
    {
        return [
            'obj_type',
            'obj_id',
            'collection_ident',
        ];
    }

    /**
     * Fetch metadata from the current object type.
     *
     * @return array
     */
    public function dataFromObject()
    {
        $proto         = $this->proto();
        $objMetadata   = $proto->metadata();
        $adminMetadata = (isset($objMetadata['admin']) ? $objMetadata['admin'] : null);

        if (empty($adminMetadata['lists'])) {
            return [];
        }

        $collectionIdent = $this->collectionIdent();
        if (!$collectionIdent) {
            $collectionIdent = $this->collectionIdentFallback();
        }

        if ($collectionIdent && $proto->view()) {
            $collectionIdent = $proto->render($collectionIdent);
        }

        if (!$collectionIdent) {
            return [];
        }

        if (isset($adminMetadata['lists'][$collectionIdent])) {
            $objListData = $adminMetadata['lists'][$collectionIdent];
        } else {
            $objListData = [];
        }

        $collectionConfig = [];

        if (isset($objListData['orders']) && isset($adminMetadata['list_orders'])) {
            $extraOrders = array_intersect(
                array_keys($adminMetadata['list_orders']),
                array_keys($objListData['orders'])
            );
            foreach ($extraOrders as $listIdent) {
                $collectionConfig['orders'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_orders'][$listIdent],
                    $objListData['orders'][$listIdent]
                );
            }
        }

        if (isset($objListData['filters']) && isset($adminMetadata['list_filters'])) {
            $extraFilters = array_intersect(
                array_keys($adminMetadata['list_filters']),
                array_keys($objListData['filters'])
            );
            foreach ($extraFilters as $listIdent) {
                $collectionConfig['filters'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_filters'][$listIdent],
                    $objListData['filters'][$listIdent]
                );
            }
        }

        if ($collectionConfig) {
            $this->mergeCollectionConfig($collectionConfig);
        }

        return $objListData;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies HttpAwareTrait dependencies
        $this->setHttpRequest($container['request']);

        $this->setCollectionLoader($container['model/collection/loader']);

        if (isset($container['admin/config']['apis.google.map.key'])) {
            $this->setApiKey($container['admin/config']['apis.google.map.key']);
        } elseif (isset($container['config']['apis.google.map.key'])) {
            $this->setApiKey($container['config']['apis.google.map.key']);
        }
    }


    /**
     * @param  ModelInterface $obj The object with the latitude property.
     * @param  string         $key The property to retrieve.
     * @throws InvalidArgumentException If the data key is missing.
     * @return mixed
     */
    protected function getPropertyValue(ModelInterface $obj, $key)
    {
        if (!is_string($key) || $key === '') {
            throw new InvalidArgumentException('Missing latitude property.');
        }

        if (isset($obj[$key])) {
            return $obj[$key];
        }

        $data     = null;
        $segments = explode('.', $key);
        if (count($segments) > 1) {
            $data = $obj;
            foreach (explode('.', $key) as $segment) {
                $accessible = is_array($data) || $data instanceof ArrayAccess;
                if ($data instanceof ArrayAccess) {
                    $exists = $data->offsetExists($segment);
                } else {
                    $exists = array_key_exists($segment, $data);
                }

                if ($accessible && $exists) {
                    $data = $data[$segment];
                } else {
                    return null;
                }
            }
        }

        return $data;
    }

    /**
     * Retrieve the default data source filters (when setting data on an entity).
     *
     * Note: Adapted from {@see \Slim\CallableResolver}.
     *
     * @link   https://github.com/slimphp/Slim/blob/3.x/Slim/CallableResolver.php
     * @param  mixed $toResolve A callable used when merging data.
     * @return callable|null
     */
    protected function resolveDataSourceFilter($toResolve)
    {
        if (is_string($toResolve)) {
            $model = $this->proto();

            $resolved = [ $model, $toResolve ];

            // Check for Slim callable
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];

                if ($class === 'parent') {
                    $resolved = [ $model, $class.'::'.$method ];
                }
            }

            $toResolve = $resolved;
        }

        return parent::resolveDataSourceFilter($toResolve);
    }


    /**
     * @param mixed $rawPolygon The polygon information.
     * @return string
     */
    private function formatPolygon($rawPolygon)
    {
        if (is_string($rawPolygon)) {
            $polygon = explode(' ', $rawPolygon);
            $ret = [];
            foreach ($polygon as $poly) {
                $coords = explode(',', $poly);
                if (count($coords) < 2) {
                    continue;
                }
                $ret[] = [(float)$coords[0], (float)$coords[1]];
            }
        } else {
            $ret = $rawPolygon;
        }
        return json_encode($ret, true);
    }
}
