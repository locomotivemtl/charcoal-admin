<?php

namespace Charcoal\Admin;

use InvalidArgumentException;

// From PSR-7 (HTTP Messaging)
use Psr\Http\Message\UriInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-translation'
use Charcoal\Translation\TranslationString;
use Charcoal\Translation\Catalog\CatalogAwareInterface;
use Charcoal\Translation\Catalog\CatalogAwareTrait;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractWidget;

/**
 * The base Widget for the `admin` module.
 */
class AdminWidget extends AbstractWidget implements CatalogAwareInterface
{
    use CatalogAwareTrait;

    const DATA_SOURCE_REQUEST = 'request';
    const DATA_SOURCE_OBJECT  = 'object';

    /**
     * The base URI.
     *
     * @var UriInterface
     */
    protected $baseUrl;

    /**
     * Store a reference to the admin configuration.
     *
     * @var \Charcoal\Admin\Config
     */
    protected $adminConfig;

    /**
     * @var string $widgetId
     */
    public $widgetId;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $template
     */
    private $template;

    /**
     * @var string $ident
     */
    private $ident = '';

    /**
     * @var mixed $label
     */
    private $label;

    /**
     * @var string $lang
     */
    private $lang;

    /**
     * @var bool $showLabel
     */
    private $showLabel;

    /**
     * @var bool $showActions
     */
    private $showActions;

    /**
     * @var integer $priority
     */
    private $priority;

    /**
     * Extra data sources to merge when setting data on an entity.
     *
     * @var array
     */
    private $dataSources;

    /**
     * Associative array of source identifiers and options to apply when merging.
     *
     * @var array
     */
    private $dataSourceFilters = [];

    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->adminConfig = $container['admin/config'];
        $this->setBaseUrl($container['base-url']);
        $this->setModelFactory($container['model/factory']);

        // CatalogAware Depencency
        $this->setCatalog($container['translation/catalog']);
    }

    /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return AdminScript Chainable
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory()
    {
        return $this->modelFactory;
    }

    /**
     * @param string $template The UI item's template (identifier).
     * @throws InvalidArgumentException If the template identifier is not a string.
     * @return UiItemInterface Chainable
     */
    public function setTemplate($template)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'The admin widget template must be a string'
            );
        }
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function template()
    {
        if ($this->template === null) {
            return $this->type();
        }
        return $this->template;
    }

    /**
     * @param string $widgetId The widget identifier.
     * @return AdminWidget Chainable
     */
    public function setWidgetId($widgetId)
    {
        $this->widgetId = $widgetId;
        return $this;
    }

    /**
     * @return string
     */
    public function widgetId()
    {
        if (!$this->widgetId) {
            $this->widgetId = 'widget_'.uniqid();
        }
        return $this->widgetId;
    }

    /**
     * @param string $type The widget type.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return AdminWidget Chainable
     */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'The admin widget type must be a string'
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @param string $ident The widget ident.
     * @throws InvalidArgumentException If the ident is not a string.
     * @return AdminWidget (Chainable)
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'The admin widget identifier must be a string'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set extra data sources to merge when setting data on an entity.
     *
     * @param mixed $sources One or more data source identifiers to merge data from.
     *     Pass NULL to reset the entity back to default sources.
     *     Pass FALSE, an empty string or array to disable extra sources.
     * @return AdminWidget Chainable
     */
    public function setDataSources($sources)
    {
        if ($sources === null) {
            $this->dataSources = null;

            return $this;
        }

        if (!is_array($sources)) {
            $sources = [ $sources ];
        }

        foreach ($sources as $ident => $filter) {
            $this->addDataSources($ident, $filter);
        }

        return $this;
    }

    /**
     * Set extra data sources to merge when setting data on an entity.
     *
     * @param mixed $sourceIdent  The data source identifier.
     * @param mixed $sourceFilter Optional filter to apply to the source's data.
     * @throws InvalidArgumentException If the data source is invalid.
     * @return AdminWidget Chainable
     */
    protected function addDataSources($sourceIdent, $sourceFilter = null)
    {
        $validSources = $this->acceptedDataSources();

        if (is_numeric($sourceIdent) && is_string($sourceFilter)) {
            $sourceIdent   = $sourceFilter;
            $sourceFilter = null;
        }

        if (!is_string($sourceIdent)) {
            throw new InvalidArgumentException('Data source identifier must be a string');
        }

        if (!in_array($sourceIdent, $validSources)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid data source. Must be one of %s',
                    implode(', ', $validSources)
                )
            );
        }

        if ($this->dataSources === null) {
            $this->dataSources = [];
        }

        $this->dataSources[] = $sourceIdent;
        $this->dataSourceFilters[$sourceIdent] = $this->resolveDataSourceFilter($sourceFilter);

        return $this;
    }

    /**
     * Retrieve the extra data sources to merge when setting data on an entity.
     *
     * @return string[]
     */
    public function dataSources()
    {
        if ($this->dataSources === null) {
            return $this->defaultDataSources();
        }

        return $this->dataSources;
    }

    /**
     * Retrieve the available data sources (when setting data on an entity).
     *
     * @return string[]
     */
    protected function acceptedDataSources()
    {
        return [ self::DATA_SOURCE_REQUEST, self::DATA_SOURCE_OBJECT ];
    }

    /**
     * Retrieve the default data sources (when setting data on an entity).
     *
     * @return string[]
     */
    protected function defaultDataSources()
    {
        return [];
    }

    /**
     * Retrieve the default data source filters (when setting data on an entity).
     *
     * @return array
     */
    protected function defaultDataSourceFilters()
    {
        return [];
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
        if (is_callable($toResolve)) {
            return $toResolve;
        }

        $resolved = $toResolve;

        if (is_string($toResolve)) {
            // check for slim callable as "class:method"
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class  = $matches[1];
                $method = $matches[2];

                if ($class === 'parent') {
                    $resolved = [ $this, $class.'::'.$method ];
                } else {
                    if (!class_exists($class)) {
                        return null;
                    }
                    $resolved = [ $class, $method ];
                }
            } else {
                $resolved = [ $this, $toResolve ];
            }
        }

        if (!is_callable($resolved)) {
            return null;
        }

        return $resolved;
    }

    /**
     * Retrieve the callable filter for the given data source.
     *
     * @param string $sourceIdent A data source identifier.
     * @throws InvalidArgumentException If the data source is invalid.
     * @return callable|null Returns a callable variable.
     */
    public function dataSourceFilter($sourceIdent)
    {
        if (!is_string($sourceIdent)) {
            throw new InvalidArgumentException('Data source identifier must be a string');
        }

        $filters = array_merge($this->defaultDataSourceFilters(), $this->dataSourceFilters);

        if (isset($filters[$sourceIdent])) {
            return $filters[$sourceIdent];
        }

        return null;
    }

    /**
     * Retrieve the available data sources (when setting data on an entity).
     *
     * @param array|ArrayInterface $dataset The entity data.
     * @return AdminWidget Chainable
     */
    protected function mergeDataSources($dataset = null)
    {
        $sources = $this->dataSources();
        foreach ($sources as $sourceIdent) {
            $filter = $this->dataSourceFilter($sourceIdent);
            $getter = $this->camelize('data_from_'.$sourceIdent);
            $method = [ $this, $getter ];

            if (is_callable($method)) {
                $data = call_user_func($method);

                if ($data) {
                    if ($filter && $dataset) {
                        $data = call_user_func($filter, $data, $dataset);
                    }

                    parent::setData($data);
                }
            }
        }

        return $this;
    }

    /**
     * @param mixed $label The label.
     * @return AdminWidget Chainable
     */
    public function setLabel($label)
    {
        if (TranslationString::isTranslatable($label)) {
            $this->label = new TranslationString($label);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [];
    }

    /**
     * @param boolean $show The show actions flag.
     * @return AdminWidget Chainable
     */
    public function setShowActions($show)
    {
        $this->showActions = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showActions()
    {
        if ($this->showActions !== false) {
            return (count($this->actions()) > 0);
        } else {
            return false;
        }
    }

    /**
     * @param boolean $show The show label flag.
     * @return AdminWidget Chainable
     */
    public function setShowLabel($show)
    {
        $this->showLabel = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showLabel()
    {
        if ($this->showLabel !== false) {
            return !!strval($this->label());
        } else {
            return false;
        }
    }

    /**
     * Retrieve the base URI of the administration area.
     *
     * @return string|UriInterface
     */
    public function adminUrl()
    {
        $adminPath = $this->adminConfig['base_path'];

        return rtrim($this->baseUrl(), '/').'/'.rtrim($adminPath, '/').'/';
    }

    /**
     * Set the base URI of the application.
     *
     * @param string|UriInterface $uri The base URI.
     * @return self
     */
    public function setBaseUrl($uri)
    {
        $this->baseUrl = $uri;

        return $this;
    }

    /**
     * Retrieve the base URI of the application.
     *
     * @return string|UriInterface
     */
    public function baseUrl()
    {
        return rtrim($this->baseUrl, '/').'/';
    }

    /**
     * @param integer $priority The widget's sorting priority.
     * @return AdminWidget Chainable
     */
    public function setPriority($priority)
    {
        $this->priority = (int)$priority;
        return $this;
    }

    /**
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }
}
