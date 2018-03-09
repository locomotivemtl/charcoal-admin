<?php

namespace Charcoal\Admin\Widget;

use \RuntimeException;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Support\HttpAwareTrait;
use Charcoal\Admin\Ui\ActionContainerTrait;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;

/**
 * Displays a collection of models in a tabular (table) format.
 */
class TableWidget extends AdminWidget implements CollectionContainerInterface
{
    use ActionContainerTrait;
    use CollectionContainerTrait {
        CollectionContainerTrait::parsePropertyCell as parseCollectionPropertyCell;
        CollectionContainerTrait::parseObjectRow as parseCollectionObjectRow;
    }
    use HttpAwareTrait;

    /**
     * Default sorting priority for an action.
     *
     * @const integer
     */
    const DEFAULT_ACTION_PRIORITY = 10;

    /**
     * @var array $properties
     */
    protected $properties;

    /**
     * @var boolean $parsedProperties
     */
    protected $parsedProperties = false;

    /**
     * @var array $propertiesOptions
     */
    protected $propertiesOptions;

    /**
     * @var boolean $sortable
     */
    protected $sortable;

    /**
     * @var boolean $showTableHeader
     */
    protected $showTableHeader = true;

    /**
     * @var boolean $showTableHead
     */
    protected $showTableHead = true;

    /**
     * @var boolean $showTableFoot
     */
    protected $showTableFoot = false;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $widgetFactory;

    /**
     * @var FactoryInterface $propertyFactory
     */
    private $propertyFactory;

    /**
     * @var mixed $adminMetadata
     */
    private $adminMetadata;

    /**
     * List actions ars displayed by default.
     *
     * @var boolean
     */
    private $showListActions = true;

    /**
     * Store the list actions.
     *
     * @var array|null
     */
    protected $listActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    protected $defaultListActions;

    /**
     * Keep track if list actions are finalized.
     *
     * @var boolean
     */
    protected $parsedListActions = false;

    /**
     * Object actions ars displayed by default.
     *
     * @var boolean
     */
    private $showObjectActions = true;

    /**
     * Store the object actions.
     *
     * @var array|null
     */
    protected $objectActions;

    /**
     * Store the default object actions.
     *
     * @var array|null
     */
    protected $defaultObjectActions;

    /**
     * Keep track if object actions are finalized.
     *
     * @var boolean
     */
    protected $parsedObjectActions = false;

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
            'sortable',
            'template',
        ];
    }

    /**
     * Fetch metadata from the current object type.
     *
     * @return array
     */
    public function dataFromObject()
    {
        $proto = $this->proto();
        $objMetadata = $proto->metadata();
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

        if (isset($objListData['list_actions']) && isset($adminMetadata['list_actions'])) {
            $extraListActions = array_intersect(
                array_keys($adminMetadata['list_actions']),
                array_keys($objListData['list_actions'])
            );
            foreach ($extraListActions as $listIdent) {
                $objListData['list_actions'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_actions'][$listIdent],
                    $objListData['list_actions'][$listIdent]
                );
            }
        }

        if (isset($objListData['object_actions']) && isset($adminMetadata['list_object_actions'])) {
            $extraObjectActions = array_intersect(
                array_keys($adminMetadata['list_object_actions']),
                array_keys($objListData['object_actions'])
            );
            foreach ($extraObjectActions as $listIdent) {
                $objListData['object_actions'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_object_actions'][$listIdent],
                    $objListData['object_actions'][$listIdent]
                );
            }
        }

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
     * Retrieve the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        return [
            'obj_type'         => $this->objType(),
            'template'         => $this->template(),
            'collection_ident' => $this->collectionIdent(),
            'properties'       => $this->propertiesIdents(),
            'filters'          => $this->filters(),
            'orders'           => $this->orders(),
            'list_actions'     => $this->listActions(),
            'object_actions'   => $this->rawObjectActions(),
            'pagination'       => $this->pagination(),
        ];
    }

    /**
     * Sets and returns properties
     *
     * Manages which to display, and their order, as set in object metadata
     *
     * @return FormPropertyWidget[]
     */
    public function properties()
    {
        if ($this->properties === null || $this->parsedProperties === false) {
            $this->parsedProperties = true;

            $model = $this->proto();
            $properties = $model->metadata()->properties();

            $listProperties = null;
            if ($this->properties === null) {
                $collectionConfig = $this->collectionConfig();
                if (isset($collectionConfig['properties'])) {
                    $listProperties = array_flip($collectionConfig['properties']);
                }
            } else {
                $listProperties = array_flip($this->properties);
            }

            if ($listProperties) {
                // Replacing values of listProperties from index to actual property values
                $properties = array_replace($listProperties, $properties);
                // Get only the keys that are in listProperties from props
                $properties = array_intersect_key($properties, $listProperties);
            }

            $this->properties = $properties;
        }

        return $this->properties;
    }

    /**
     * Retrieve the property keys shown in the collection.
     *
     * @return array
     */
    public function propertiesIdents()
    {
        $collectionConfig = $this->collectionConfig();
        if (isset($collectionConfig['properties'])) {
            return $collectionConfig['properties'];
        }

        return [];
    }

    /**
     * Retrieve the property customizations for the collection.
     *
     * @return array|null
     */
    public function propertiesOptions()
    {
        if ($this->propertiesOptions === null) {
            $this->propertiesOptions = $this->defaultPropertiesOptions();
        }

        return $this->propertiesOptions;
    }



    /**
     * Retrieve the view options for the given property.
     *
     * @param  string $propertyIdent The property identifier to lookup.
     * @return array
     */
    public function viewOptions($propertyIdent)
    {
        if (!$propertyIdent) {
            return [];
        }

        if ($propertyIdent instanceof PropertyInterface) {
            $propertyIdent = $propertyIdent->ident();
        }

        $options = $this->propertiesOptions();

        if (isset($options[$propertyIdent]['view_options'])) {
            return $options[$propertyIdent]['view_options'];
        } else {
            return [];
        }
    }

    /**
     * Properties to display in collection template, and their order, as set in object metadata
     *
     * @return array|Generator
     */
    public function collectionProperties()
    {
        $props = $this->properties();

        foreach ($props as $propertyIdent => $property) {
            $propertyMetadata = $props[$propertyIdent];

            $p = $this->propertyFactory()->create($propertyMetadata['type']);
            $p->setIdent($propertyIdent);
            $p->setData($propertyMetadata);

            $column = [
                'label' => trim($p->label())
            ];

            $column['classes'] = $this->parsePropertyCellClasses($p);
            if (is_array($column['classes'])) {
                $column['classes'] = implode(' ', array_unique($column['classes']));
            }

            if (empty($column['classes'])) {
                unset($column['classes']);
            }

            yield $column;
        }
    }

    /**
     * Show/hide the table's object actions.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the actions.
     * @return TableWidget Chainable
     */
    public function setShowObjectActions($show)
    {
        $this->showObjectActions = !!$show;

        return $this;
    }

    /**
     * Determine if the table's object actions should be shown.
     *
     * @return boolean
     */
    public function showObjectActions()
    {
        if ($this->showObjectActions === false) {
            return false;
        } else {
            return count($this->objectActions());
        }
    }

    /**
     * Retrieve the table's object actions.
     *
     * @return array
     */
    public function objectActions()
    {
        $this->rawObjectActions();

        $objectActions = [];
        if (is_array($this->objectActions)) {
            $objectActions = $this->parseAsObjectActions($this->objectActions);
        }

        return $objectActions;
    }

    /**
     * Retrieve the table's object actions without rendering it.
     *
     * @return array
     */
    public function rawObjectActions()
    {
        if ($this->objectActions === null) {
            $parsed = $this->parsedObjectActions;

            $collectionConfig = $this->collectionConfig();
            if (isset($collectionConfig['object_actions'])) {
                $actions = $collectionConfig['object_actions'];
            } else {
                $actions = [];
            }
            $this->setObjectActions($actions);

            $this->parsedObjectActions = $parsed;
        }

        if ($this->parsedObjectActions === false) {
            $this->parsedObjectActions = true;
            $this->objectActions = $this->createObjectActions($this->objectActions);
        }

        return $this->objectActions;
    }

    /**
     * Set the table's object actions.
     *
     * @param  array $actions One or more actions.
     * @return TableWidget Chainable.
     */
    public function setObjectActions(array $actions)
    {
        $this->parsedObjectActions = false;

        $this->objectActions = $this->mergeActions($this->defaultObjectActions(), $actions);

        return $this;
    }

    /**
     * Build the table's object actions (row).
     *
     * Object actions should come from the collection settings defined by the "collection_ident".
     * It is still possible to completly override those externally by setting the "object_actions"
     * with the {@see self::setObjectActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array Object actions.
     */
    public function createObjectActions(array $actions)
    {
        $objectActions = $this->parseActions($actions);

        return $objectActions;
    }

    /**
     * Parse the given actions as (row) object actions.
     *
     * @param  array $actions Actions to resolve.
     * @return array
     */
    protected function parseAsObjectActions(array $actions)
    {
        $objectActions = [];
        foreach ($actions as $action) {
            $action = $this->parseActionRenderables($action, true);

            if (isset($action['ident'])) {
                if ($action['ident'] === 'view' && !$this->isObjViewable()) {
                    $action['active'] = false;
                } elseif ($action['ident'] === 'create' && !$this->isObjCreatable()) {
                    $action['active'] = false;
                } elseif ($action['ident'] === 'edit' && !$this->isObjEditable()) {
                    $action['active'] = false;
                } elseif ($action['ident'] === 'delete' && !$this->isObjDeletable()) {
                    $action['active'] = false;
                }
            }

            if ($action['actions']) {
                $action['actions']    = $this->parseAsObjectActions($action['actions']);
                $action['hasActions'] = !!array_filter($action['actions'], function ($action) {
                    return $action['active'];
                });
            }

            $objectActions[] = $action;
        }

        return $objectActions;
    }



    /**
     * Determine if the table's empty collection actions should be shown.
     *
     * @return boolean
     */
    public function showEmptyListActions()
    {
        $actions = $this->emptyListActions();

        return count($actions);
    }

    /**
     * Retrieve the table's empty collection actions.
     *
     * @return array
     */
    public function emptyListActions()
    {
        $actions = $this->listActions();

        $filteredArray = array_filter($actions, function ($action) {
            return $action['empty'];
        });

        return array_values($filteredArray);
    }

    /**
     * Show/hide the table's collection actions.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the actions.
     * @return TableWidget Chainable
     */
    public function setShowListActions($show)
    {
        $this->showListActions = !!$show;

        return $this;
    }

    /**
     * Determine if the table's collection actions should be shown.
     *
     * @return boolean
     */
    public function showListActions()
    {
        if ($this->showListActions === false) {
            return false;
        } else {
            return count($this->listActions());
        }
    }

    /**
     * Retrieve the table's collection actions.
     *
     * @return array
     */
    public function listActions()
    {
        if ($this->listActions === null) {
            $collectionConfig = $this->collectionConfig();
            if (isset($collectionConfig['list_actions'])) {
                $actions = $collectionConfig['list_actions'];
            } else {
                $actions = [];
            }
            $this->setListActions($actions);
        }

        if ($this->parsedListActions === false) {
            $this->parsedListActions = true;
            $this->listActions = $this->createListActions($this->listActions);
        }

        return $this->listActions;
    }


    /**
     * @return PaginationWidget
     */
    public function paginationWidget()
    {
        $pagination = $this->widgetFactory()->create(PaginationWidget::class);
        $pagination->setData([
            'page'         => $this->page(),
            'num_per_page' => $this->numPerPage(),
            'num_total'    => $this->numTotal(),
            'label'        => $this->translator()->translation('Objects list navigation')
        ]);

        return $pagination;
    }

    /**
     * @param boolean $show The show flag.
     * @return TableWidget Chainable
     */
    public function setShowTableHeader($show)
    {
        $this->showTableHeader = !!$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showTableHeader()
    {
        return $this->showTableHeader;
    }

    /**
     * @param boolean $show The show flag.
     * @return TableWidget Chainable
     */
    public function setShowTableHead($show)
    {
        $this->showTableHead = !!$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showTableHead()
    {
        return $this->showTableHead;
    }

    /**
     * @param boolean $show The show flag.
     * @return TableWidget Chainable
     */
    public function setShowTableFoot($show)
    {
        $this->showTableFoot = !!$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showTableFoot()
    {
        return $this->showTableFoot;
    }

    /**
     * @param boolean $sortable The sortable flag.
     * @return TableWidget Chainable
     */
    public function setSortable($sortable)
    {
        $this->sortable = !!$sortable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function sortable()
    {
        return $this->sortable;
    }

    /**
     * @return string
     */
    public function jsActionPrefix()
    {
        return ($this->currentObj) ? 'js-obj' : 'js-list';
    }

    /**
     * Generate URL for editing an object
     * @return string
     */
    public function objectEditUrl()
    {
        return 'object/edit?main_menu={{ main_menu }}&obj_type='.$this->objType();
    }

    /**
     * Generate URL for creating an object
     * @return string
     */
    public function objectCreateUrl()
    {
        $actions = $this->listActions();
        if ($actions) {
            foreach ($actions as $action) {
                if (isset($action['ident']) && $action['ident'] === 'create') {
                    if (isset($action['url'])) {
                        $model = $this->proto();
                        if ($model->view()) {
                            $action['url'] = $model->render((string)$action['url']);
                        } else {
                            $action['url'] = preg_replace('~{{\s*id\s*}}~', $this->currentObjId, $action['url']);
                        }

                        return $action['url'];
                    }
                }
            }
        }

        return $this->objectEditUrl();
    }

    /**
     * Determine if the object can be created.
     *
     * If TRUE, the "Create" button is shown. Objects can still be
     * inserted programmatically or via direct action on the database.
     *
     * @return boolean
     */
    public function isObjCreatable()
    {
        $model = $this->proto();
        $method = [ $model, 'isCreatable' ];

        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be modified.
     *
     * If TRUE, the "Modify" button is shown. Objects can still be
     * updated programmatically or via direct action on the database.
     *
     * @return boolean
     */
    public function isObjEditable()
    {
        $model = ($this->currentObj) ? $this->currentObj : $this->proto();
        $method = [ $model, 'isEditable' ];

        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be deleted.
     *
     * If TRUE, the "Delete" button is shown. Objects can still be
     * deleted programmatically or via direct action on the database.
     *
     * @return boolean
     */
    public function isObjDeletable()
    {
        $model  = ($this->currentObj) ? $this->currentObj : $this->proto();
        $method = [ $model, 'isDeletable' ];

        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be viewed (on the front-end).
     *
     * If TRUE, any "View" button is shown. The object can still be
     * saved programmatically.
     *
     * @return boolean
     */
    public function isObjViewable()
    {
        $model = ($this->currentObj) ? $this->currentObj : $this->proto();
        if (!$model->id()) {
            return false;
        }

        $method = [ $model, 'isViewable' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies HttpAwareTrait dependencies
        $this->setHttpRequest($container['request']);

        $this->setView($container['view']);
        $this->setCollectionLoader($container['model/collection/loader']);
        $this->setWidgetFactory($container['widget/factory']);
        $this->setPropertyFactory($container['property/factory']);
        $this->setPropertyDisplayFactory($container['property/display/factory']);
    }

    /**
     * Retrieve the widget factory.
     *
     * @throws RuntimeException If the widget factory was not previously set.
     * @return FactoryInterface
     */
    protected function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            throw new RuntimeException(
                sprintf('Widget Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->widgetFactory;
    }

    /**
     * @throws RuntimeException If the property factory was not previously set / injected.
     * @return FactoryInterface
     */
    protected function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new RuntimeException(
                'Property factory is not set for table widget'
            );
        }

        return $this->propertyFactory;
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

            // check for slim callable as "class:method"
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
     * Set the table's collection actions.
     *
     * @param  array $actions One or more actions.
     * @return TableWidget Chainable.
     */
    protected function setListActions(array $actions)
    {
        $this->parsedListActions = false;

        $this->listActions = $this->mergeActions($this->defaultListActions(), $actions);

        return $this;
    }

    /**
     * Build the table collection actions.
     *
     * List actions should come from the collection settings defined by the "collection_ident".
     * It is still possible to completly override those externally by setting the "list_actions"
     * with the {@see self::setListActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array List actions.
     */
    protected function createListActions(array $actions)
    {
        $this->actionsPriority = $this->defaultActionPriority();

        $listActions = $this->parseAsListActions($actions);

        return $listActions;
    }

    /**
     * Parse the given actions as collection actions.
     *
     * @param  array $actions Actions to resolve.
     * @return array
     */
    protected function parseAsListActions(array $actions)
    {
        $listActions = [];
        foreach ($actions as $ident => $action) {
            $ident  = $this->parseActionIdent($ident, $action);
            $action = $this->parseActionItem($action, $ident, true);

            if (!isset($action['priority'])) {
                $action['priority'] = $this->actionsPriority++;
            }

            if ($action['ident'] === 'create') {
                $action['empty'] = true;

                if (!$this->isObjCreatable()) {
                    $action['active'] = false;
                }
            } else {
                $action['empty'] = (isset($action['empty']) ? boolval($action['empty']) : false);
            }

            if (is_array($action['actions'])) {
                $action['actions']    = $this->parseAsListActions($action['actions']);
                $action['hasActions'] = !!array_filter($action['actions'], function ($action) {
                    return $action['active'];
                });
            }

            if (isset($listActions[$ident])) {
                $hasPriority = ($action['priority'] > $listActions[$ident]['priority']);
                if ($hasPriority || $action['isSubmittable']) {
                    $listActions[$ident] = array_replace($listActions[$ident], $action);
                } else {
                    $listActions[$ident] = array_replace($action, $listActions[$ident]);
                }
            } else {
                $listActions[$ident] = $action;
            }
        }

        usort($listActions, [ $this, 'sortActionsByPriority' ]);

        while (($first = reset($listActions)) && $first['isSeparator']) {
            array_shift($listActions);
        }

        while (($last = end($listActions)) && $last['isSeparator']) {
            array_pop($listActions);
        }

        return $listActions;
    }

    /**
     * Retrieve the table's default collection actions.
     *
     * @return array
     */
    protected function defaultListActions()
    {
        if ($this->defaultListActions === null) {
            $this->defaultListActions = [];
        }

        return $this->defaultListActions;
    }

    /**
     * Retrieve the table's default object actions.
     *
     * @return array
     */
    protected function defaultObjectActions()
    {
        if ($this->defaultObjectActions === null) {
            $edit = [
                'label'    => $this->translator()->translation('Modify'),
                'url'      => $this->objectEditUrl().'&obj_id={{id}}',
                'ident'    => 'edit',
                'priority' => 1
            ];
            $this->defaultObjectActions = [ $edit ];
        }

        return $this->defaultObjectActions;
    }

    /**
     * Retrieve the default property customizations.
     *
     * The default configset is determined by the collection ident and object type, if assigned.
     *
     * @return array|null
     */
    protected function defaultPropertiesOptions()
    {
        $collectionConfig = $this->collectionConfig();

        if (empty($collectionConfig['properties_options'])) {
            return [];
        }

        return $collectionConfig['properties_options'];
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
        $cell = $this->parseCollectionPropertyCell($object, $property, $propertyValue);

        $cell['classes'] = $this->parsePropertyCellClasses($property, $object);
        if (is_array($cell['classes'])) {
            $cell['classes'] = implode(' ', array_unique($cell['classes']));
        }

        if (empty($cell['classes'])) {
            unset($cell['classes']);
        }

        return $cell;
    }

    /**
     * Filter the table cell's CSS classes before the property is assigned
     * to the object row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  PropertyInterface   $property The current property.
     * @param  ModelInterface|null $object   Optional. The current row's object.
     * @return array
     */
    protected function parsePropertyCellClasses(
        PropertyInterface $property,
        ModelInterface $object = null
    ) {
        unset($object);

        $ident = $property->ident();
        $classes = [ sprintf('property-%s', $ident) ];
        $options = $this->viewOptions($ident);

        if (isset($options['classes'])) {
            if (is_array($options['classes'])) {
                $classes = array_merge($classes, $options['classes']);
            } else {
                $classes[] = $options['classes'];
            }
        }

        return $classes;
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
        $row = $this->parseCollectionObjectRow($object, $objectProperties);
        $row['objectActions'] = $this->objectActions();
        $row['showObjectActions'] = ($this->showObjectActions === false) ? false : !!$row['objectActions'];

        return $row;
    }

    /**
     * Set an widget factory.
     *
     * @param FactoryInterface $factory The factory to create widgets.
     * @return void
     */
    private function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;
    }

    /**
     * @param FactoryInterface $factory The property factory, to create properties.
     * @return TableWidget Chainable
     */
    private function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;

        return $this;
    }
}
