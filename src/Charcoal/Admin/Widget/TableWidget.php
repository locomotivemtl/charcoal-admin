<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Admin\AdminWidget as AdminWidget;

use \Charcoal\Property\PropertyFactory as PropertyFactory;
use \Charcoal\Property\PropertyInterface as PropertyInterface;

use \Charcoal\Admin\Ui\CollectionContainerInterface as CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait as CollectionContainerTrait;

/**
* The table widget displays a collection in a tabular (table) format.
*/
class TableWidget extends AdminWidget implements CollectionContainerInterface
{
    use CollectionContainerTrait;

    /**
    * @var array $_properties
    */
    protected $_properties;

    /**
    * @var $_properties_options
    */
    protected $_properties_options;

    /**
    * @var array $_orders
    */
    protected $_orders;

    /**
    * @var array $_filters
    */
    protected $_filters;

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

        $this->set_collection_data($data);

        if (isset($data['collection_ident']) && $data['collection_ident'] !== null) {
            $this->set_collection_ident($data['collection_ident']);
        }

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
            throw new InvalidArgumentException('Collection ident must be a string');
        }
        $this->_collection_ident = $collection_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function collection_ident()
    {
        return $this->_collection_ident;
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
        if ($this->_properties === null) {
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

            $this->_properties = $props;
        }

        return $this->_properties;
    }

    /**
    * Properties to display in collection template, and their order, as set in object metadata
    * @return  FormPropertyWidget         Generator function
    */
    public function collection_properties()
    {
        $props = $this->properties();

        foreach ($props as $property_ident => $property) {
            $property_metadata = $props[$property_ident];

            $p = PropertyFactory::instance()->get($property_metadata['type']);
            $p->set_ident($property_ident);
            $p->set_data($property_metadata);

            $column = [
                'label' => $p->label()
            ];

            yield $column;
        }
    }

    /**
    * @return boolean
    */
    public function show_object_actions()
    {
        return true;
    }

    /**
    * @return array
    */
    public function object_actions()
    {
        return [
            [
                'label'=>'Edit',
                'ident'=>'edit'
            ],
            [
                'label'=>'Quick Edit',
                'ident'=>'quick-edit'
            ],
            [
                'label'=>'Inline Edit',
                'ident'=>'inline-edit'
            ],
            [
                'label'=>'Delete',
                'ident'=>'delete'
            ]
        ];
    }

    /**
    * @return array
    */
    public function list_actions()
    {
        return [
            [
                'label'=>'Create New',
                'ident'=>'create'
            ],
            [
                'label'=>'Quick Create',
                'ident'=>'quick-create'
            ],
            [
                'label'=>'Reorder',
                'ident'=>'reorder'
            ]
        ];
    }

    /**
    * @return array
    */
    public function sublist_actions()
    {
        return [
            [
                'label'=>'Inline Edit',
                'ident'=>'inline-edit'
            ],
            [
                'label'=>'Delete',
                'ident'=>'Delete'
            ]
        ];
    }

    /**
    * @return boolean
    */
    public function show_table_header()
    {
        return true;
    }

    /**
    * @return boolean
    */
    public function show_table_footer()
    {
        return false;
    }


}
