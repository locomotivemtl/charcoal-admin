<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Charcoal;
use \Charcoal\Property\PropertyFactory as PropertyFactory;
use \Charcoal\Property\PropertyInterface as PropertyInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminWidget as AdminWidget;
use \Charcoal\Admin\Ui\CollectionContainerInterface as CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait as CollectionContainerTrait;

/**
 * The table widget displays a collection in a tabular (table) format.
 */
class SearchWidget extends AdminWidget implements CollectionContainerInterface
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
     * @param array $data The search widget data.
     * @return TableWidget Chainable
     */
    public function setData(array $data)
    {

        $objData = $this->dataFromObject();
        $data = array_merge_recursive($objData, $data);

        parent::setData($data);

        return $this;
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
                'Collection ident must be a string'
            );
        }
        $this->collectionIdent = $collectionIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function collectionIdent()
    {
        return $this->collectionIdent;
    }

    /**
     * Fetch metadata from current obj_type
     * @return array List of metadata
     */
    public function dataFromObject()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $collectionIdent = $this->collectionIdent();
        if (!$collectionIdent) {
            $collectionIdent = isset($admin_metadata['defaultList']) ? $admin_metadata['defaultList'] : '';
        }

        $objListData = isset($admin_metadata['lists'][$collectionIdent]) ? $admin_metadata['lists'][$collectionIdent] : [];

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
                $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;

                if (isset($admin_metadata['lists'][$collectionIdent]['properties'])) {
                    // Flipping to have property ident as key
                    $listProperties = array_flip($admin_metadata['lists'][$collectionIdent]['properties']);
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
     * Properties to display in collection template, and their order, as set in object metadata
     * @return  FormPropertyWidget         Generator function
     */
    public function jsonPropertiesList()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $collectionIdent = $this->collectionIdent();

        if (isset($admin_metadata['lists'][$collectionIdent]['properties'])) {
            $props = $admin_metadata['lists'][$collectionIdent]['properties'];
        }
        return json_encode($props);
    }
}
