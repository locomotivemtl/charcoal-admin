<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Admin\Widget as Widget;
use \Charcoal\Admin\Ui\CollectionContainerInterface as CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait as CollectionContainerTrait;

/**
* The table widget displays a collection in a tabular (table) format.
*/
class Table extends Widget implements CollectionContainerInterface
{
    use CollectionContainerTrait;

    protected $_properties;
    protected $_properties_options;

    protected $_orders;
    protected $_filters;

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        //parent::__construct($data);

        if (is_array($data)) {
            $this->set_data($data);
           
        }
    }

    /**
    * @var array $data
    * @return Table Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);
        $this->set_collection_data($data);

        return $this;
    }

    public function show_object_actions()
    {
        return true;
    }

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
