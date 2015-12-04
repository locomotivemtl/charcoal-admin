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

    private $property_factory;

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
    public function collection_properties()
    {
        $props = $this->properties();

        foreach ($props as $property_ident => $property) {
            $property_metadata = $props[$property_ident];

            $p = $this->property_factory()->get($property_metadata['type']);
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
            // [
            //     'label' => 'Quick Edit',
            //     'ident' => 'quick-edit',
            //     'is_button' => true
            // ],
            // [
            //     'label' => 'Inline Edit',
            //     'ident' => 'inline-edit',
            //     'is_button' => true
            // ],
            // [
            //     'label' => 'Delete',
            //     'ident' => 'delete',
            //     'is_button' => true
            // ]
        ];
    }

    /**
    * @return boolean
    */
    public function has_object_actions()
    {
        $actions = $this->object_actions();
        return (count($actions) > 0);
    }

    /**
    * @return array
    */
    public function list_actions()
    {
        return [
            [
                'label' => 'Créer un nouveau',
                'ident' => 'create',
                'url' => $this->object_edit_url(),
                'widget_type' => ''
            ],
            [
                'label' => 'Importer une liste',
                'ident' => 'import',
                'is_button' => true,
                'widget_type' => 'charcoal/admin/widget/dialog/importlist'
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

    /**
    * Generate URL for editing object
    * @return string
    */
    public function object_edit_url()
    {
        return Charcoal::config()->get('URL').'admin/object/edit?obj_type='.$this->obj_type();
    }

    /**
    * @return PropertyFactory
    */
    private function property_factory()
    {
        if($this->property_factory === null) {
            $this->property_factory = new PropertyFactory();
        }
        return $this->property_factory;
    }
}
