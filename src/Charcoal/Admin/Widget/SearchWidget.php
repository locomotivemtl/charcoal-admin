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
    * @var $properties_options
    */
    protected $properties_options;

    /**
    * @var array $orders
    */
    protected $orders;

    /**
    * @var array $filters
    */
    protected $filters;

    /**
    * @param array $data Optional
    */
    /*
    public function __construct(array $data = null)
    {
        //parent::__construct($data);

        if (is_array($data)) {
            $this->set_data($data);

        }
    }
    */

    /**
    * @param array $data
    * @return TableWidget Chainable
    */
    public function set_data(array $data)
    {

        $obj_data = $this->data_from_object();
        $data = array_merge_recursive($obj_data, $data);

        parent::set_data($data);

        return $this;
    }

    /**
    * @param string $collection_ident
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_ident($collection_ident)
    {
        if (!is_string($collection_ident)) {
            throw new InvalidArgumentException(
                'Collection ident must be a string'
            );
        }
        $this->collection_ident = $collection_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function collection_ident()
    {
        return $this->collection_ident;
    }

    /**
    * Fetch metadata from current obj_type
    * @return array List of metadata
    */
    public function data_from_object()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $collection_ident = $this->collection_ident();
        if (!$collection_ident) {
            $collection_ident = isset($admin_metadata['default_list']) ? $admin_metadata['default_list'] : '';
        }

        $obj_list_data = isset($admin_metadata['lists'][$collection_ident]) ? $admin_metadata['lists'][$collection_ident] : [];

        return $obj_list_data;
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

            $collection_ident = $this->collection_ident();

            if ($collection_ident) {
                $metadata = $obj->metadata();
                $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;

                if (isset($admin_metadata['lists'][$collection_ident]['properties'])) {
                    // Flipping to have property ident as key
                    $list_properties = array_flip($admin_metadata['lists'][$collection_ident]['properties']);
                    // Replacing values of list_properties from index to actual property values
                    $props = array_replace($list_properties, $props);
                    // Get only the keys that are in list_properties from props
                    $props = array_intersect_key($props, $list_properties);
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
    public function json_properties_list()
    {
        $obj = $this->proto();
        $metadata = $obj->metadata();
        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $collection_ident = $this->collection_ident();

        if (isset($admin_metadata['lists'][$collection_ident]['properties'])) {
            $props = $admin_metadata['lists'][$collection_ident]['properties'];
        }
        return json_encode( $props );
    }


}
