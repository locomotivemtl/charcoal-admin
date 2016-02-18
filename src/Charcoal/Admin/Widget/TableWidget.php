<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

use \Charcoal\Charcoal;

use \Charcoal\Admin\AdminWidget;

use \Charcoal\Property\PropertyFactory;
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
     * @var $propertiesOptions
     */
    protected $propertiesOptions;

    /**
     * @var array $orders
     */
    protected $orders;

    /**
     * @var array $filters
     */
    protected $filters;

    /**
     * @var PropertyFactory $propertyFactory
     */
    private $propertyFactory;

    /**
     * Fetch metadata from current obj_type
     * @return array List of metadata
     */
    public function dataFromObject()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $adminMetadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $collectionIdent = $this->collectionIdent();
        if (!$collectionIdent) {
            $collectionIdent = isset($adminMetadata['default_list']) ? $adminMetadata['default_list'] : '';
        }

        $objListData = isset($adminMetadata['lists'][$collectionIdent]) ? $adminMetadata['lists'][$collectionIdent] : [];

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
     * Always return an array
     * Theses are the properties options used in the list display
     * @param  string $ident Property ident
     * @return array         []
     */
    public function propertiesOptions()
    {
        $collectionMeta = $this->collectionMeta();

        if (empty($collectionMeta)) {
            return [];
        }

        if (!isset($collectionMeta['properties_options'])) {
            return [];
        }

        return $collectionMeta['properties_options'];

    }

    /**
     * Get view options for perticular property
     * @param  string $ident [description]
     * @return array         [description]
     */
    public function viewOptions($ident)
    {
        if (!$ident) {
            return [];
        }

        $propertiesOptions = $this->propertiesOptions();
        if (!isset($propertiesOptions[$ident])) {
            return [];
        }

        if (!isset($propertiesOptions[$ident]['view_options'])) {
            return [];
        }

        return $propertiesOptions[$ident]['view_options'];

    }

    /**
     * Return the current collection metadata
     * @return array    metadata()->admin->lists->collectionIdent || []
     */
    public function collectionMeta()
    {
        $collectionIdent = $this->collectionIdent();
        if (!$collectionIdent) {
            return [];
        }

        $obj = $this->proto();
        $metadata = $obj->metadata();

        $adminmeta = isset($metadata['admin']) ? $metadata['admin'] : [];

        if (!isset($adminmeta['lists'])) {
            return [];
        }

        if (!isset($adminmeta['lists'][$collectionIdent])) {
            return [];
        }

        return $adminmeta['lists'][$collectionIdent];
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

            $p = $this->propertyFactory()->get($propertyMetadata['type'], [
                'logger' => $this->logger
            ]);
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
        return [/*
            [
                'label'     => 'Quick Edit',
                'ident'     => 'quick-edit',
                'is_button' => true
            ],
            [
                'label'     => 'Inline Edit',
                'ident'     => 'inline-edit',
                'is_button' => true
            ],
            [
                'label'     => 'Delete',
                'ident'     => 'delete',
                'is_button' => true
            ]
        */];
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
        $obj = $this->proto();
        $props = $obj->metadata()->properties();
        $collectionIdent = $this->collectionIdent();
        if ($collectionIdent) {
                $metadata = $obj->metadata();
                $adminMetadata = isset($metadata['admin']) ? $metadata['admin'] : null;
                $listOptions = $adminMetadata['lists'][$collectionIdent];

                $listActions = isset($listOptions['list_actions']) ? $listOptions['list_actions'] : [];
                return $listActions;

        } else {
            return [];
        }
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

    /**
     * @return PropertyFactory
     */
    private function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            $this->propertyFactory = new PropertyFactory();
        }
        return $this->propertyFactory;
    }
}
