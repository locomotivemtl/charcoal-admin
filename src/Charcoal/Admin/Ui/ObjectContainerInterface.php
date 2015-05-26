<?php

namespace Charcoal\Admin\Ui;

interface ObjectContainerInterface
{
    /**
    * @param array $data
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_data($data);

    /**
    * @param string $obj_type
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_type($obj_type);

    /**
    * @return string
    */
    public function obj_type();

    /**
    * @param mixed $obj_id
    * @return ObjectContainerInterface Chainable
    */
    public function set_obj_id($obj_id);

    /**
    * @return mixed
    */
    public function obj_id();

    /**
    * @return Object
    */
    public function obj();

}
