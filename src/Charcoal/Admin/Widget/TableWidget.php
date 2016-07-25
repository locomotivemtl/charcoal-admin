<?php

namespace Charcoal\Admin\Widget;

use \Exception;
use \InvalidArgumentException;

use \Pimple\Container;

use \Charcoal\Translation\TranslationString;

use \Charcoal\Factory\FactoryInterface;

use \Charcoal\Admin\AdminWidget;

use \Charcoal\Property\PropertyInterface;

use \Charcoal\Admin\Ui\CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait;

/**
 * The table widget displays a collection in a tabular (table) format.
 */
class TableWidget extends AdminWidget implements CollectionContainerInterface
{
    use CollectionContainerTrait;

    /**
     * @var array $properties
     */
    protected $properties;

    /**
     * @var boolean $sortable
     */
    protected $sortable;

    /**
     * @var FactoryInterface $propertyFactory
     */
    private $propertyFactory;

    /**
     * @var mixed $adminMetadata
     */
    private $adminMetadata;

    /**
     * @var array $listActions
     */
    private $listActions;

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setView($container['view']);
        $this->setPropertyFactory($container['property/factory']);
        $this->setPropertyDisplayFactory($container['property/display/factory']);
    }

    /**
     * @param FactoryInterface $factory The property factory, to create properties.
     * @return TableWidget Chainable
     */
    public function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the property factory was not previously set / injected.
     * @return FactoryInterface
     */
    protected function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new Exception(
                'Property factory is not set for table widget'
            );
        }
        return $this->propertyFactory;
    }

    /**
     * Fetch metadata from current obj_type
     * @return array List of metadata
     */
    public function dataFromObject()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $adminMeta = (isset($metadata['admin']) ? $metadata['admin'] : null);

        if (!isset($adminMeta['lists']) || empty($adminMeta['lists'])) {
            return [];
        }

        $collectionIdent = $this->collectionIdent();
        if (!$collectionIdent) {
            $collectionIdent = (isset($adminMeta['default_list']) ? $adminMeta['default_list'] : '');
        }

        $objListData = (isset($adminMeta['lists'][$collectionIdent]) ? $adminMeta['lists'][$collectionIdent] : []);

        return $objListData;
    }

    /**
     * Sets and returns properties
     * Manages which to display, and their order, as set in object metadata
     * @return  FormPropertyWidget  Generator function
     */
    public function properties()
    {
        if ($this->properties === null) {
            $obj = $this->proto();
            $props = $obj->metadata()->properties();

            $collectionIdent = $this->collectionIdent();

            if ($collectionIdent) {
                $metadata = $obj->metadata();
                $adminMetadata = isset($metadata['admin']) ? $metadata['admin'] : null;

                if (isset($adminMetadata['lists'][$collectionIdent]['properties'])) {
                    // Flipping to have property ident as key
                    $listProperties = array_flip($adminMetadata['lists'][$collectionIdent]['properties']);
                    // Replacing values of listProperties from index to actual property values
                    $props = array_replace($listProperties, $props);
                    // Get only the keys that are in listProperties from props
                    $props = array_intersect_key($props, $listProperties);
                }
            }

            $this->properties = $props;
        }

        return $this->properties;
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
     * Always return an array
     * Theses are the properties options used in the list display
     * @return array
     */
    public function propertiesOptions()
    {
        $collectionConfig = $this->collectionConfig();

        if (empty($collectionConfig)) {
            return [];
        }

        if (!isset($collectionConfig['properties_options'])) {
            return [];
        }

        return $collectionConfig['properties_options'];
    }

    /**
     * Get view options for perticular property
     * @param  string $ident The ident of the view options.
     * @return array         [description]
     */
    public function viewOptions($ident)
    {
        if (!$ident) {
            return [];
        }

        $propertiesOptions = $this->propertiesOptions();

        if (isset($propertiesOptions[$ident]['view_options'])) {
            return $propertiesOptions[$ident]['view_options'];
        } else {
            return [];
        }
    }

    /**
     * Properties to display in collection template, and their order, as set in object metadata
     * @return  FormPropertyWidget         Generator function
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
                'label' => $p->label()
            ];

            yield $column;
        }
    }

    /**
     * @return boolean
     */
    public function showObjectActions()
    {
        return true;
    }

    /**
     * @return array
     */
    public function objectActions()
    {
        $obj = $this->proto();
        $props = $obj->metadata()->properties();
        $collectionIdent = $this->collectionIdent();

        if (!$collectionIdent) {
            return [];
        }

        $metadata = $obj->metadata();
        $adminMetadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $listOptions = $adminMetadata['lists'][$collectionIdent];

        $objectActions = isset($listOptions['object_actions']) ? $listOptions['object_actions'] : [];
        foreach ($objectActions as &$action) {
            if (isset($action['url'])) {
                if ($obj->view() !== null) {
                    $action['url'] = $obj->render($action['url']);
                } else {
                    $action['url'] = str_replace('{{id}}', $this->currentObjId, $action['url']);
                }
                $action['url'] = $this->adminUrl().$action['url'];
            } else {
                $action['url'] = '#';
            }
        }
        return $objectActions;
    }

    /**
     * @return array
     */
    public function defaultObjectActions()
    {
        return [
            'label' => new TranslationString('Modifier'),
            'url'   => $this->objectEditUrl().'&amp;obj_id={{id}}',
            'ident' => 'edit'
        ];
    }

    /**
     * @return boolean
     */
    public function hasObjectActions()
    {
        $actions = $this->objectActions();
        return (count($actions) > 0);
    }

    /**
     * @return array
     */
    public function listActions()
    {
        if (!$this->listActions) {
            $this->listActions = $this->createListActions();
        }
        return $this->listActions;
    }

    /**
     * Set list actions
     * @param array $listActions List actions.
     * @return TableWidget Chainable.
     */
    public function setListActions(array $listActions)
    {
        $this->listActions = $listActions;
        return $this;
    }

    /**
     * Default list actions behavior.
     * List actions should come from the collection data
     * pointed out by the collectionIdent.
     * It is still possible to completly override those
     * externally by setting the listActions with the
     * setListActions setter.
     *
     * @return array List actions.
     */
    public function createListActions()
    {
        $collectionConfig = $this->collectionConfig();
        if ($collectionConfig) {
            $listActions   = (isset($collectionConfig['list_actions']) ? $collectionConfig['list_actions'] : []);
            foreach ($listActions as &$action) {
                if (isset($action['label'])) {
                    $action['label'] = new TranslationString($action['label']);
                }
            }
            return $listActions;
        } else {
            return [];
        }
    }


    /**
     * @return PaginationWidget
     */
    public function paginationWidget()
    {
        $pagination = new PaginationWidget([
            'logger'=>$this->logger
        ]);
        $pagination->setData([
            'page' => $this->page(),
            'num_per_page' => $this->numPerPage(),
            'num_total' => $this->numTotal()
        ]);
        return $pagination;
    }

    /**
     * @return array
     */
    public function sublistActions()
    {
        return [
            [
                'label' => 'Inline Edit',
                'ident' => 'inline-edit'
            ],
            [
                'label' => 'Delete',
                'ident' => 'Delete'
            ]
        ];
    }

    /**
     * @return boolean
     */
    public function showTableHeader()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function showTableFooter()
    {
        return false;
    }

    /**
     * Generate URL for editing object
     * @return string
     */
    public function objectEditUrl()
    {
        return 'object/edit?obj_type='.$this->objType();
    }
}
