<?php

namespace Charcoal\Admin\Ui;

interface CollectionContainerInterface
{
    /**
    * @param array $data
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_data($data);

    /**
    * @param string $obj_type
    * @return CollectionContainerInterface Chainable
    */
    public function set_obj_type($obj_type);

    /**
    * @return string
    */
    public function obj_type();

    /**
    * @param mixed $dashboard_config
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_config($dashboard_config);

    /**
    * @return mixed
    */
    public function collection_config();

    /**
    * @param array $data
    * @return mixed
    */
    //public function create_collection_config($data = null);


    /**
    * @param mixed $collection
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection($collection);

    /**
    * @return Collection
    */
    public function collection();

    /**
    * @return array
    */
    public function objects();

    /**
    * @return boolean
    */
    public function has_objects();

    /**
    * @return Object
    */
    public function proto();

}
