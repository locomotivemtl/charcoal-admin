<?php

namespace Charcoal\Admin\Ui;

interface CollectionContainerInterface
{
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
    * @param string $collection_ident
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_collection_ident($collection_ident);

    /**
    * @return string|null
    */
    public function collection_ident();

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
    * @param integer $page
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_page($page);

    /**
    * @return integer
    */
    public function page();

    /**
    * @param integer $num_per_page
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function set_num_per_page($num_per_page);

    /**
    * @return integer
    */
    public function num_per_page();

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
